<?php declare(strict_types = 1);

namespace Contributte\Translation\DI;

use Contributte;
use Nette;
use Nette\Schema\Expect;
use Psr;
use ReflectionClass;
use ReflectionException;
use stdClass;
use Symfony;
use Tracy;

/**
 * @property      stdClass $config
 */
class TranslationExtension extends Nette\DI\CompilerExtension
{

	public function getConfigSchema(): Nette\Schema\Schema
	{
		$builder = $this->getContainerBuilder();

		return Expect::structure([
			'debug' => Expect::bool($builder->parameters['debugMode']),
			'debugger' => Expect::bool(interface_exists(Tracy\IBarPanel::class)),
			'factory' => Expect::string()->default(null),
			'logger' => Expect::mixed()->default(null),
			'locales' => Expect::structure([
				'whitelist' => Expect::array()->default(null)->assert(function (array $array): bool {
					if (count($array) !== count(array_unique($array))) {
						throw new Contributte\Translation\Exceptions\InvalidArgument('Whitelist settings have not unique values.');
					}

					return true;
				}),
				'default' => Expect::string('en'),
				'fallback' => Expect::array()->default(null),
			])->assert(function (stdClass $locales): bool {
				if ($locales->whitelist !== null && !in_array($locales->default, $locales->whitelist, true)) {
					throw new Contributte\Translation\Exceptions\InvalidArgument('If you set whitelist, default locale must be on him.');
				}

				return true;
			}),
			'localeResolvers' => Expect::array()->default(null),
			'loaders' => Expect::array()->default([
				'neon' => Contributte\Translation\Loaders\Neon::class,
			]),
			'dirs' => Expect::array()->default([]),
			'cache' => Expect::structure([
				'dir' => Expect::string($builder->parameters['tempDir'] . '/cache/translation'),
				'factory' => Expect::string(Symfony\Component\Config\ConfigCacheFactory::class),
				'vary' => Expect::array()->default([]),
			]),
			'translatorFactory' => Expect::string()->default(null),
			'returnOriginalMessage' => Expect::bool()->default(false),
			'autowired' => Expect::type('bool|array')->default(true),
		]);
	}

	/**
	 * @throws Contributte\Translation\Exceptions\InvalidArgument|ReflectionException
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		if ($this->config->locales->fallback === null) {
			$this->config->locales->fallback = ['en_US'];
		}

		if ($this->config->localeResolvers === null) {
			$this->config->localeResolvers = [
				Contributte\Translation\LocalesResolvers\Session::class,
				Contributte\Translation\LocalesResolvers\Router::class,
				Contributte\Translation\LocalesResolvers\Parameter::class,
				Contributte\Translation\LocalesResolvers\Header::class,
			];
		}

		// LocaleResolver
		$localeResolver = $builder->addDefinition($this->prefix('localeResolver'))
			->setFactory(Contributte\Translation\LocaleResolver::class);

		// LocaleResolvers
		$localeResolvers = [];

		foreach ($this->config->localeResolvers as $v1) {
			$reflection = new ReflectionClass($v1);

			if (!$reflection->implementsInterface(Contributte\Translation\LocalesResolvers\ResolverInterface::class)) {
				throw new Contributte\Translation\Exceptions\InvalidArgument('Resolver must implement interface "' . Contributte\Translation\LocalesResolvers\ResolverInterface::class . '".');
			}

			$localeResolvers[] = $builder->addDefinition($this->prefix('localeResolver' . $reflection->getShortName()))
				->setFactory($v1);

			$localeResolver->addSetup('addResolver', [$v1]);
		}

		// FallbackResolver
		$builder->addDefinition($this->prefix('fallbackResolver'))
			->setFactory(Contributte\Translation\FallbackResolver::class);

		// ConfigCacheFactory
		$reflection = new ReflectionClass($this->config->cache->factory);

		if (!$reflection->implementsInterface(Symfony\Component\Config\ConfigCacheFactoryInterface::class)) {
			throw new Contributte\Translation\Exceptions\InvalidArgument('Cache factory must implement interface "' . Symfony\Component\Config\ConfigCacheFactoryInterface::class . '".');
		}

		$configCacheFactory = $builder->addDefinition($this->prefix('configCacheFactory'))
			->setFactory($this->config->cache->factory, [$this->config->debug]);

		$autowired = [];

		if ($this->config->autowired === true) {
			$autowired = [
				Nette\Localization\ITranslator::class,
				Symfony\Contracts\Translation\TranslatorInterface::class,
				Contributte\Translation\Translator::class,
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

			if (!$reflectionTranslatorFactory->isSubclassOf(Contributte\Translation\Translator::class)) {
				throw new Contributte\Translation\Exceptions\InvalidArgument('Translator must extends class "' . Contributte\Translation\Translator::class . '".');
			}

			$factory = $this->config->translatorFactory;

			if ($this->config->autowired) {
				$autowired[] = $factory;
			}
		} else {
			$factory = Contributte\Translation\Translator::class;
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
			$reflection = new ReflectionClass($v1);

			if (!$reflection->implementsInterface(Symfony\Component\Translation\Loader\LoaderInterface::class)) {
				throw new Contributte\Translation\Exceptions\InvalidArgument('Loader must implement interface "' . Symfony\Component\Translation\Loader\LoaderInterface::class . '".');
			}

			$loader = $builder->addDefinition($this->prefix('loader' . Nette\Utils\Strings::firstUpper($k1)))
				->setFactory($v1);

			$translator->addSetup('addLoader', [$k1, $loader]);
		}

		// Tracy\Panel
		if (!$this->config->debug || !$this->config->debugger) {
			return;
		}

		$tracyPanel = $builder->addDefinition($this->prefix('tracyPanel'))
			->setFactory(Contributte\Translation\Tracy\Panel::class, [$translator]);

		foreach ($localeResolvers as $v1) {
			$tracyPanel->addSetup('addLocaleResolver', [$v1]);
		}
	}

	/**
	 * @throws Contributte\Translation\Exceptions\InvalidArgument|ReflectionException
	 */
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		/** @var Nette\DI\Definitions\ServiceDefinition $translator */
		$translator = $builder->getDefinition($this->prefix('translator'));
		$whitelistRegexp = Contributte\Translation\Helpers::whitelistRegexp($this->config->locales->whitelist);

		if ($this->config->debug && $this->config->debugger) {
			/** @var Nette\DI\Definitions\ServiceDefinition $tracyPanel */
			$tracyPanel = $builder->getDefinition($this->prefix('tracyPanel'));
		}

		$latteFactoryName = $builder->getByType(Nette\Bridges\ApplicationLatte\ILatteFactory::class);

		if ($latteFactoryName !== null) {
			$latteFilters = $builder->addDefinition($this->prefix('latte.filters'))
				->setFactory(Contributte\Translation\Latte\Filters::class, [$translator]);

			/** @var Nette\DI\Definitions\FactoryDefinition $latteFactory */
			$latteFactory = $builder->getDefinition($latteFactoryName);

			$latteFactory->getResultDefinition()
				->addSetup('?->onCompile[] = function (Latte\\Engine $engine): void { ?::install($engine->getCompiler()); }', ['@self', new Nette\PhpGenerator\PhpLiteral(Contributte\Translation\Latte\Macros::class)])
				->addSetup('addProvider', ['translator', $builder->getDefinition($this->prefix('translator'))])
				->addSetup('addFilter', ['translate', [$latteFilters, 'translate']]);
		}

		/** @var Contributte\Translation\DI\TranslationProviderInterface $v1 */
		foreach ($this->compiler->getExtensions(TranslationProviderInterface::class) as $v1) {
			$this->config->dirs = array_merge($v1->getTranslationResources(), $this->config->dirs);
		}

		if (count($this->config->dirs) > 0) {
			foreach ($this->config->loaders as $k1 => $v1) {
				foreach (Nette\Utils\Finder::find('*.' . $k1)->from($this->config->dirs) as $v2) {
					$match = Nette\Utils\Strings::match($v2->getFilename(), '~^(?P<domain>.*?)\.(?P<locale>[^\.]+)\.(?P<format>[^\.]+)$~');

					if ($match === null) {
						continue;
					}

					if ($whitelistRegexp !== null && Nette\Utils\Strings::match($match['locale'], $whitelistRegexp) === null) {
						if (isset($tracyPanel)) {
							$tracyPanel->addSetup('addIgnoredResource', [$match['format'], $v2->getPathname(), $match['locale'], $match['domain']]);
						}

						continue;
					}

					$translator->addSetup('addResource', [$match['format'], $v2->getPathname(), $match['locale'], $match['domain']]);

					if (!isset($tracyPanel)) {
						continue;
					}

					$tracyPanel->addSetup('addResource', [$match['format'], $v2->getPathname(), $match['locale'], $match['domain']]);
				}
			}
		}

		if ($this->config->logger === null) {
			return;
		}

		// Psr\Log\LoggerInterface
		if ($this->config->logger === true) {
			$psrLogger = $builder->getDefinitionByType(Psr\Log\LoggerInterface::class);

		} elseif (is_string($this->config->logger) && class_exists($this->config->logger)) {
			$reflection = new ReflectionClass($this->config->logger);

			if (!$reflection->implementsInterface(Psr\Log\LoggerInterface::class)) {
				throw new Contributte\Translation\Exceptions\InvalidArgument('Logger must implement interface "' . Psr\Log\LoggerInterface::class . '".');
			}

			try {
				$psrLogger = $builder->getDefinitionByType($this->config->logger);

			} catch (Nette\DI\MissingServiceException $e) {
				$psrLogger = $builder->addDefinition($this->prefix('psrLogger'))
					->setFactory($this->config->logger);
			}
		} else {
			throw new Contributte\Translation\Exceptions\InvalidArgument('Option "logger" must be bool for autowired or class name as string.');
		}

		$translator->addSetup('setPsrLogger', [$psrLogger]);
	}

	public function afterCompile(Nette\PhpGenerator\ClassType $class): void
	{
		if (!$this->config->debug || !$this->config->debugger) {
			return;
		}

		$initialize = $class->getMethod('initialize');
		$initialize->addBody('$this->getService(?)->addPanel($this->getService(?));', ['tracy.bar', $this->prefix('tracyPanel')]);
	}

}
