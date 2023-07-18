<?php declare(strict_types = 1);

namespace Contributte\Translation\DI;

use Contributte\Translation\DI\Helpers as DIHelpers;
use Contributte\Translation\Exceptions\InvalidArgument;
use Contributte\Translation\FallbackResolver;
use Contributte\Translation\Helpers;
use Contributte\Translation\Latte\Filters;
use Contributte\Translation\Latte\Macros;
use Contributte\Translation\Latte\TranslatorExtension;
use Contributte\Translation\Loaders\Neon;
use Contributte\Translation\LocaleResolver;
use Contributte\Translation\LocalesResolvers\Header;
use Contributte\Translation\LocalesResolvers\Parameter;
use Contributte\Translation\LocalesResolvers\ResolverInterface;
use Contributte\Translation\LocalesResolvers\Router;
use Contributte\Translation\LocalesResolvers\Session;
use Contributte\Translation\Tracy\Panel;
use Contributte\Translation\Translator;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\MissingServiceException;
use Nette\Localization\ITranslator;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpLiteral;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nette\Utils\Finder;
use Nette\Utils\Strings;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use stdClass;
use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tracy\IBarPanel;

/**
 * @property stdClass $config
 */
class TranslationExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		$builder = $this->getContainerBuilder();

		return Expect::structure([
			'debug' => Expect::bool($builder->parameters['debugMode']),
			'debugger' => Expect::bool(interface_exists(IBarPanel::class)),
			'factory' => Expect::string()->default(null),
			'logger' => Expect::mixed()->default(null),
			'locales' => Expect::structure([
				'whitelist' => Expect::array()->default(null)->assert(static function (array $array): bool {
					if (count($array) !== count(array_unique($array))) {
						throw new InvalidArgument('Whitelist settings have not unique values.');
					}

					return true;
				}),
				'default' => Expect::string('en'),
				'fallback' => Expect::array()->default(null),
			])->assert(static function (stdClass $locales): bool {
				if ($locales->whitelist !== null && !in_array($locales->default, $locales->whitelist, true)) {
					throw new InvalidArgument('If you set whitelist, default locale must be on him.');
				}

				return true;
			}),
			'localeResolvers' => Expect::array()->default(null),
			'loaders' => Expect::array()->default([
				'neon' => Neon::class,
			]),
			'dirs' => Expect::array()->default([]),
			'cache' => Expect::structure([
				'dir' => Expect::string($builder->parameters['tempDir'] . '/cache/translation'),
				'factory' => Expect::string(ConfigCacheFactory::class),
				'vary' => Expect::array()->default([]),
			]),
			'translatorFactory' => Expect::string()->default(null),
			'returnOriginalMessage' => Expect::bool()->default(true),
			'autowired' => Expect::type('bool|array')->default(true),
			'latteFactory' => Expect::string(ILatteFactory::class)->nullable(),
		]);
	}

	/**
	 * @throws \Contributte\Translation\Exceptions\InvalidArgument|\ReflectionException
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		if ($this->config->locales->fallback === null) {
			$this->config->locales->fallback = ['en_US'];
		}

		if ($this->config->localeResolvers === null) {
			$this->config->localeResolvers = [
				Session::class,
				Router::class,
				Parameter::class,
				Header::class,
			];
		}

		// LocaleResolver
		$localeResolver = $builder->addDefinition($this->prefix('localeResolver'))
			->setFactory(LocaleResolver::class);

		// LocaleResolvers
		$localeResolvers = [];

		foreach ($this->config->localeResolvers as $v1) {
			$reflection = new ReflectionClass(DIHelpers::unwrapEntity($v1));

			if (!$reflection->implementsInterface(ResolverInterface::class)) {
				throw new InvalidArgument('Resolver must implement interface "' . ResolverInterface::class . '".');
			}

			$localeResolvers[] = $builder->addDefinition($this->prefix('localeResolver' . $reflection->getShortName()))
				->setFactory($v1);

			$localeResolver->addSetup('addResolver', [$v1]);
		}

		// FallbackResolver
		$builder->addDefinition($this->prefix('fallbackResolver'))
			->setFactory(FallbackResolver::class);

		// ConfigCacheFactory
		$reflection = new ReflectionClass($this->config->cache->factory);

		if (!$reflection->implementsInterface(ConfigCacheFactoryInterface::class)) {
			throw new InvalidArgument('Cache factory must implement interface "' . ConfigCacheFactoryInterface::class . '".');
		}

		$configCacheFactory = $builder->addDefinition($this->prefix('configCacheFactory'))
			->setFactory($this->config->cache->factory, [$this->config->debug]);

		$autowired = [];

		if ($this->config->autowired === true) {
			$autowired = [
				ITranslator::class,
				TranslatorInterface::class,
				Translator::class,
			];

		} elseif (is_array($this->config->autowired)) {
			$autowired = $this->config->autowired;
		}

		if (is_array($this->config->autowired)) {
			$autowired = $this->config->autowired;
		}

		// Translator
		if ($this->config->translatorFactory !== null) {
			$reflectionTranslatorFactory = new ReflectionClass($this->config->translatorFactory);

			if (!$reflectionTranslatorFactory->isSubclassOf(Translator::class)) {
				throw new InvalidArgument('Translator must extends class "' . Translator::class . '".');
			}

			$factory = $this->config->translatorFactory;

			if ($this->config->autowired) {
				$autowired[] = $factory;
			}
		} else {
			$factory = Translator::class;
		}

		$translator = $builder->addDefinition($this->prefix('translator'))
			->setFactory($factory, ['defaultLocale' => $this->config->locales->default, 'cacheDir' => $this->config->cache->dir, 'debug' => $this->config->debug, 'cacheVary' => $this->config->cache->vary])
			->addSetup('setLocalesWhitelist', [$this->config->locales->whitelist])
			->addSetup('setConfigCacheFactory', [$configCacheFactory])
			->addSetup('setFallbackLocales', [$this->config->locales->fallback])
			->addSetup('$returnOriginalMessage', [$this->config->returnOriginalMessage]);

		if ($this->config->autowired === false) {
			$translator->setAutowired(false);
		} else {
			$translator->setAutowired($autowired);
		}

		// Loaders
		foreach ($this->config->loaders as $k1 => $v1) {
			$reflection = new ReflectionClass(DIHelpers::unwrapEntity($v1));

			if (!$reflection->implementsInterface(LoaderInterface::class)) {
				throw new InvalidArgument('Loader must implement interface "' . LoaderInterface::class . '".');
			}

			$loader = $builder->addDefinition($this->prefix('loader' . Strings::firstUpper($k1)))
				->setFactory($v1);

			$translator->addSetup('addLoader', [$k1, $loader]);
		}

		// Tracy\Panel
		if (!$this->config->debug || !$this->config->debugger) {
			return;
		}

		$tracyPanel = $builder->addDefinition($this->prefix('tracyPanel'))
			->setFactory(Panel::class, [$translator]);

		foreach ($localeResolvers as $v1) {
			$tracyPanel->addSetup('addLocaleResolver', [$v1]);
		}
	}

	/**
	 * @throws \Contributte\Translation\Exceptions\InvalidArgument|\ReflectionException
	 */
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		/** @var \Nette\DI\Definitions\ServiceDefinition $translator */
		$translator = $builder->getDefinition($this->prefix('translator'));
		$whitelistRegexp = Helpers::whitelistRegexp($this->config->locales->whitelist);

		if ($this->config->debug && $this->config->debugger) {
			/** @var \Nette\DI\Definitions\ServiceDefinition $tracyPanel */
			$tracyPanel = $builder->getDefinition($this->prefix('tracyPanel'));
		}

		$latteFactoryName = $this->config->latteFactory ? $builder->getByType($this->config->latteFactory) : null;

		if ($latteFactoryName !== null) {
			$iTranslator = $builder->getDefinitionByType(ITranslator::class);

			$latteFilters = $builder->addDefinition($this->prefix('latte.filters'))
				->setFactory(Filters::class);

			/** @var \Nette\DI\Definitions\FactoryDefinition $latteFactory */
			$latteFactory = $builder->getDefinition($latteFactoryName);

			/** @phpstan-ignore-next-line */
			if (version_compare(\Latte\Engine::VERSION, '3', '<')) {
				$latteFactory->getResultDefinition()
					->addSetup('?->onCompile[] = function (Latte\\Engine $engine): void { ?::install($engine->getCompiler()); }', ['@self', new PhpLiteral(Macros::class)])
					->addSetup('addProvider', ['translator', $iTranslator])
					->addSetup('addFilter', ['translate', [$latteFilters, 'translate']]);
			} else {
				$latteExtension = $builder->addDefinition($this->prefix('latte.extension'))
					->setFactory(TranslatorExtension::class);
				$latteFactory->getResultDefinition()
					->addSetup('addExtension', [$latteExtension]);
			}
		}

		/** @var \Contributte\Translation\DI\TranslationProviderInterface $v1 */
		foreach ($this->compiler->getExtensions(TranslationProviderInterface::class) as $v1) {
			$this->config->dirs = array_merge($v1->getTranslationResources(), $this->config->dirs);
		}

		if (count($this->config->dirs) > 0) {
			foreach ($this->config->loaders as $k1 => $v1) {
				/** @var array<\SplFileInfo> $finder */
				$finder = Finder::find('*.' . $k1)
					->from($this->config->dirs);

				foreach ($finder as $fileInfo) {
					$match = Strings::match($fileInfo->getFilename(), '~^(?P<domain>.*?)\.(?P<locale>[^\.]+)\.(?P<format>[^\.]+)$~');

					if ($match === null) {
						continue;
					}

					if ($whitelistRegexp !== null && Strings::match($match['locale'], $whitelistRegexp) === null) {
						if (isset($tracyPanel)) {
							$tracyPanel->addSetup('addIgnoredResource', [$fileInfo->getPathname(), $match['locale'], $match['domain']]);
						}

						continue;
					}

					$translator->addSetup('addResource', [$match['format'], $fileInfo->getPathname(), $match['locale'], $match['domain']]);

					if (!isset($tracyPanel)) {
						continue;
					}

					$tracyPanel->addSetup('addResource', [$fileInfo->getPathname(), $match['locale'], $match['domain']]);
				}
			}
		}

		if ($this->config->logger === null) {
			return;
		}

		// \Psr\Log\LoggerInterface
		if ($this->config->logger === true) {
			$psrLogger = $builder->getDefinitionByType(LoggerInterface::class);

		} elseif (is_string($this->config->logger) && class_exists($this->config->logger)) {
			$reflection = new ReflectionClass($this->config->logger);

			if (!$reflection->implementsInterface(LoggerInterface::class)) {
				throw new InvalidArgument('Logger must implement interface "' . LoggerInterface::class . '".');
			}

			try {
				$psrLogger = $builder->getDefinitionByType($this->config->logger);

			} catch (MissingServiceException $e) {
				$psrLogger = $builder->addDefinition($this->prefix('psrLogger'))
					->setFactory($this->config->logger);
			}
		} else {
			throw new InvalidArgument('Option "logger" must be bool for autowired or class name as string.');
		}

		$translator->addSetup('setPsrLogger', [$psrLogger]);
	}

	public function afterCompile(
		ClassType $class
	): void
	{
		if (!$this->config->debug || !$this->config->debugger) {
			return;
		}

		$initialize = $class->getMethod('initialize');
		$initialize->addBody('$this->getService(?)->addPanel($this->getService(?));', ['tracy.bar', $this->prefix('tracyPanel')]);
	}

}
