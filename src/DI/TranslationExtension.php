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
use Nette\DI\Definitions\Statement;
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
use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tracy\IBarPanel;

/**
 * @phpstan-type ExtensionConfig array{
 *     debug: bool,
 *     debugger: bool,
 *     factory: string|null,
 *     logger: bool|class-string|null,
 *     locales: array{
 *         whitelist: array<string>|null,
 *         default: string,
 *         fallback: array<string>|null,
 *     },
 *     localeResolvers: array<class-string|Statement>|null,
 *     loaders: array<string, class-string|Statement>,
 *     dirs: array<string>,
 *     cache: array{
 *         dir: string,
 *         factory: class-string,
 *         vary: array<string>,
 *     },
 *     translatorFactory: class-string|null,
 *     returnOriginalMessage: bool,
 *     autowired: bool|array<class-string>,
 *     latteFactory: class-string|null,
 * }
 * @property ExtensionConfig $config
 */
class TranslationExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		$builder = $this->getContainerBuilder();
		$tempDir = $builder->parameters['tempDir'];

		if (!is_string($tempDir)) {
			throw new InvalidArgument('Container parameter "tempDir" must be a string.');
		}

		return Expect::structure([
			'debug' => Expect::bool($builder->parameters['debugMode']),
			'debugger' => Expect::bool(interface_exists(IBarPanel::class)),
			'factory' => Expect::string()->default(null),
			'logger' => Expect::mixed()->default(null),
			'locales' => Expect::structure(
				[
					'whitelist' => Expect::listOf('string')
						->default(null)
						->assert(
							static function (
								mixed $array
							): bool {
								/** @phpstan-var array<string> $array */
								if (count($array) !== count(array_unique($array))) {
									throw new InvalidArgument('Whitelist settings have not unique values.');
								}

								return true;
							}
						),
					'default' => Expect::string('en'),
					'fallback' => Expect::listOf('string')->default(null),
				]
			)
			->castTo('array')
			->assert(
				static function (
					mixed $locales
				): bool {
					/** @phpstan-var array{whitelist: array<string>|null, default: string, fallback: array<string>|null} $locales */
					if ($locales['whitelist'] !== null && !in_array($locales['default'], $locales['whitelist'], true)) {
						throw new InvalidArgument('If you set whitelist, default locale must be on him.');
					}

					return true;
				}
			),
			'localeResolvers' => Expect::array()->default(null),
			'loaders' => Expect::array()->default([
				'neon' => Neon::class,
			]),
			'dirs' => Expect::listOf('string')->default([]),
			'cache' => Expect::structure([
				'dir' => Expect::string($tempDir . '/cache/translation'),
				'factory' => Expect::string(ConfigCacheFactory::class),
				'vary' => Expect::listOf('string')->default([]),
			])->castTo('array'),
			'translatorFactory' => Expect::string()->default(null),
			'returnOriginalMessage' => Expect::bool()->default(true),
			'autowired' => Expect::type('bool|array')->default(true),
			'latteFactory' => Expect::string(ILatteFactory::class)->nullable(),
		])->castTo('array');
	}

	/**
	 * @throws \Contributte\Translation\Exceptions\InvalidArgument|\ReflectionException
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->config;

		$fallbackLocales = $config['locales']['fallback'] ?? ['en_US'];
		$localeResolverClasses = $config['localeResolvers'] ?? [
			Session::class,
			Router::class,
			Parameter::class,
			Header::class,
		];

		// LocaleResolver
		$localeResolver = $builder->addDefinition($this->prefix('localeResolver'))
			->setFactory(LocaleResolver::class);

		// LocaleResolvers
		$localeResolvers = [];

		foreach ($localeResolverClasses as $v1) {
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
		$reflection = new ReflectionClass($config['cache']['factory']);

		if (!$reflection->implementsInterface(ConfigCacheFactoryInterface::class)) {
			throw new InvalidArgument('Cache factory must implement interface "' . ConfigCacheFactoryInterface::class . '".');
		}

		$configCacheFactory = $builder->addDefinition($this->prefix('configCacheFactory'))
			->setFactory($config['cache']['factory'], [$config['debug']]);

		$autowired = [];

		if ($config['autowired'] === true) {
			$autowired = [
				ITranslator::class,
				TranslatorInterface::class,
				Translator::class,
			];

		} elseif (is_array($config['autowired'])) {
			$autowired = $config['autowired'];
		}

		// Translator
		if ($config['translatorFactory'] !== null) {
			$reflectionTranslatorFactory = new ReflectionClass($config['translatorFactory']);

			if (!$reflectionTranslatorFactory->isSubclassOf(Translator::class)) {
				throw new InvalidArgument('Translator must extends class "' . Translator::class . '".');
			}

			$factory = $config['translatorFactory'];

			if ($config['autowired'] !== false) {
				$autowired[] = $factory;
			}
		} else {
			$factory = Translator::class;
		}

		$translator = $builder->addDefinition($this->prefix('translator'))
			->setFactory($factory, ['defaultLocale' => $config['locales']['default'], 'cacheDir' => $config['cache']['dir'], 'debug' => $config['debug'], 'cacheVary' => $config['cache']['vary']])
			->addSetup('setLocalesWhitelist', [$config['locales']['whitelist']])
			->addSetup('setConfigCacheFactory', [$configCacheFactory])
			->addSetup('setFallbackLocales', [$fallbackLocales])
			->addSetup('$returnOriginalMessage', [$config['returnOriginalMessage']]);

		if ($config['autowired'] === false) {
			$translator->setAutowired(false);
		} else {
			$translator->setAutowired($autowired);
		}

		// Loaders
		foreach ($config['loaders'] as $k1 => $v1) {
			$reflection = new ReflectionClass(DIHelpers::unwrapEntity($v1));

			if (!$reflection->implementsInterface(LoaderInterface::class)) {
				throw new InvalidArgument('Loader must implement interface "' . LoaderInterface::class . '".');
			}

			$loader = $builder->addDefinition($this->prefix('loader' . Strings::firstUpper($k1)))
				->setFactory($v1);

			$translator->addSetup('addLoader', [$k1, $loader]);
		}

		// Tracy\Panel
		if (!$config['debug'] || !$config['debugger']) {
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
		$config = $this->config;

		/** @var \Nette\DI\Definitions\ServiceDefinition $translator */
		$translator = $builder->getDefinition($this->prefix('translator'));
		$whitelistRegexp = Helpers::whitelistRegexp($config['locales']['whitelist']);

		if ($config['debug'] && $config['debugger']) {
			/** @var \Nette\DI\Definitions\ServiceDefinition $tracyPanel */
			$tracyPanel = $builder->getDefinition($this->prefix('tracyPanel'));
		}

		$latteFactoryName = $config['latteFactory'] !== null ? $builder->getByType($config['latteFactory']) : null;

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

		/** @var array<TranslationProviderInterface> $providers */
		$providers = $this->compiler->getExtensions(TranslationProviderInterface::class); // @phpstan-ignore-line
		$dirs = $config['dirs'];
		foreach ($providers as $v1) {
			$dirs = array_merge($v1->getTranslationResources(), $dirs);
		}

		if (count($dirs) > 0) {
			foreach ($config['loaders'] as $k1 => $v1) {
				$finder = Finder::find('*.' . $k1)
					->from(array_values($dirs));

				foreach ($finder as $fileInfo) {
					if (!$fileInfo instanceof \SplFileInfo) {
						continue;
					}

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

		if ($config['logger'] === null) {
			return;
		}

		// \Psr\Log\LoggerInterface
		if ($config['logger'] === true) {
			$psrLogger = $builder->getDefinitionByType(LoggerInterface::class);

		} elseif (is_string($config['logger']) && class_exists($config['logger'])) {
			$reflection = new ReflectionClass($config['logger']);

			if (!$reflection->implementsInterface(LoggerInterface::class)) {
				throw new InvalidArgument('Logger must implement interface "' . LoggerInterface::class . '".');
			}

			try {
				$psrLogger = $builder->getDefinitionByType($config['logger']);

			} catch (MissingServiceException $e) {
				$psrLogger = $builder->addDefinition($this->prefix('psrLogger'))
					->setFactory($config['logger']);
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
		if (!$this->config['debug'] || !$this->config['debugger']) {
			return;
		}

		$initialize = $class->getMethod('initialize');
		$initialize->addBody('$this->getService(?)->addPanel($this->getService(?));', ['tracy.bar', $this->prefix('tracyPanel')]);
	}

}
