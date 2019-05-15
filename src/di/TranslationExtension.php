<?php

/**
 * This file is part of the Translette/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation\DI;

use Nette;
use Symfony;
use Contributte;
use Tracy;


/**
 * @author Ales Wita
 * @author Filip Prochazka
 */
class TranslationExtension extends Nette\DI\CompilerExtension
{
	/** @var array */
	public $defaults = [
		'debug' => null, // null is auto detect
		'debugger' => null, // null is auto detect
		'locales' => [
			'whitelist' => null,
			'default' => null,
			'fallback' => ['en_US'],
		],
		'localeResolvers' => [
			Contributte\Translation\LocalesResolvers\Session::class,
			//Contributte\Translation\LocalesResolvers\Router::class,
			Contributte\Translation\LocalesResolvers\Parameter::class,
			Contributte\Translation\LocalesResolvers\Header::class,
		],
		'loaders' => [
			'neon' => Contributte\Translation\Loaders\Neon::class,
		],
		'dirs' => [],
		'cache' => [
			'dir' => null, // null is auto detect
			'factory' => Symfony\Component\Config\ConfigCacheFactory::class,
		],
	];


	/**
	 * @throws Contributte\Translation\InvalidArgumentException|\ReflectionException
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults, $this->config);

		if ($config['debug'] === null) {
			$this->defaults['debug'] = $config['debug'] = $builder->parameters['debugMode'];
		}

		if ($config['debugger'] === null) {
			$this->defaults['debugger'] = $config['debugger'] = interface_exists(Tracy\IBarPanel::class);
		}

		if ($config['cache']['dir'] === null) {
			$this->defaults['cache']['dir'] = $config['cache']['dir'] = $builder->parameters['tempDir'] . '/cache/translation';
		}


		// LocaleResolver
		$localeResolver = $builder->addDefinition($this->prefix('localeResolver'))
			->setFactory(Contributte\Translation\LocaleResolver::class);


		// LocaleResolvers
		$localeResolvers = [];

		foreach ($config['localeResolvers'] as $v1) {
			$reflection = new \ReflectionClass($v1);

			if (!$reflection->implementsInterface(Contributte\Translation\LocalesResolvers\ResolverInterface::class)) {
				throw new Contributte\Translation\InvalidArgumentException('Resolver must implement interface "' . Contributte\Translation\LocalesResolvers\ResolverInterface::class . '".');
			}

			$localeResolvers[] = $resolver = $builder->addDefinition($this->prefix('localeResolver' . $reflection->getShortName()))
				->setFactory($v1);

			$localeResolver->addSetup('addResolver', [$resolver]);
		}


		// FallbackResolver
		$builder->addDefinition($this->prefix('fallbackResolver'))
			->setFactory(Contributte\Translation\FallbackResolver::class);


		// ConfigCacheFactory
		$reflection = new \ReflectionClass($config['cache']['factory']);

		if (!$reflection->implementsInterface(Symfony\Component\Config\ConfigCacheFactoryInterface::class)) {
			throw new Contributte\Translation\InvalidArgumentException('Cache factory must implement interface "' . Symfony\Component\Config\ConfigCacheFactoryInterface::class . '".');
		}

		$configCacheFactory = $builder->addDefinition($this->prefix('configCacheFactory'))
			->setFactory($config['cache']['factory'], [$config['debug']]);


		// Translator
		if ($config['locales']['default'] === null) {
			throw new Contributte\Translation\InvalidArgumentException('Default locale must be set.');
		}

		$translator = $builder->addDefinition($this->prefix('translator'))
			->setType(Nette\Localization\ITranslator::class)
			->setFactory(Contributte\Translation\Translator::class, ['defaultLocale' => $config['locales']['default'], 'cacheDir' => $config['cache']['dir'], 'debug' => $config['debug']])
			->addSetup('setLocalesWhitelist', [$config['locales']['whitelist']])
			->addSetup('setConfigCacheFactory', [$configCacheFactory])
			->addSetup('setFallbackLocales', [$config['locales']['fallback']]);


		// Loaders
		foreach ($config['loaders'] as $k1 => $v1) {
			$reflection = new \ReflectionClass($v1);

			if (!$reflection->implementsInterface(Symfony\Component\Translation\Loader\LoaderInterface::class)) {
				throw new Contributte\Translation\InvalidArgumentException('Loader must implement interface "' . Symfony\Component\Translation\Loader\LoaderInterface::class . '".');
			}

			$loader = $builder->addDefinition($this->prefix('loader' . Nette\Utils\Strings::firstUpper($k1)))
				->setFactory($v1);

			$translator->addSetup('addLoader', [$k1, $loader]);
		}


		// Tracy\Panel
		if ($config['debug'] && $config['debugger']) {
			$tracyPanel = $builder->addDefinition($this->prefix('tracyPanel'))
				->setFactory(Contributte\Translation\Tracy\Panel::class, [$translator]);

			foreach ($localeResolvers as $v1) {
				$tracyPanel->addSetup('addLocaleResolver', [$v1]);
			}
		}
	}


	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults, $this->config);

		/** @var Nette\DI\ServiceDefinition $translator */
		$translator = $builder->getDefinition($this->prefix('translator'));
		$whitelistRegexp = Contributte\Translation\Helpers::whitelistRegexp($config['locales']['whitelist']);

		if ($config['debug'] && $config['debugger']) {
			/** @var Nette\DI\ServiceDefinition $tracyPanel */
			$tracyPanel = $builder->getDefinition($this->prefix('tracyPanel'));
		}

		$templateFactoryName = $builder->getByType(Nette\Application\UI\ITemplateFactory::class);

		if ($templateFactoryName !== null) {
			/** @var Nette\DI\ServiceDefinition $templateFactory */
			$templateFactory = $builder->getDefinition($templateFactoryName);

			$templateFactory->addSetup('
					$service->onCreate[] = function (Nette\\Bridges\\ApplicationLatte\\Template $template): void {
						$template->setTranslator(?);
					};', [$translator]);
		}

		if ($builder->hasDefinition('latte.latteFactory')) {
			/** @var Nette\DI\ServiceDefinition $latteFactory */
			$latteFactory = $builder->getDefinition('latte.latteFactory');

			$latteFactory->addSetup('?->onCompile[] = function (Latte\\Engine $engine): void { ?::install($engine->getCompiler()); }', ['@self', new Nette\PhpGenerator\PhpLiteral(Contributte\Translation\Latte\Macros::class)])
				->addSetup('addProvider', ['translator', $builder->getDefinition($this->prefix('translator'))]);
		}

		/** @var Contributte\Translation\DI\TranslationProviderInterface $v1 */
		foreach ($this->compiler->getExtensions(TranslationProviderInterface::class) as $v1) {
			$config['dirs'] = array_merge($v1->getTranslationResources(), $config['dirs']);
		}

		if (count($config['dirs']) > 0) {
			foreach ($config['loaders'] as $k1 => $v1) {
				foreach (Nette\Utils\Finder::find('*.' . $k1)->from($config['dirs']) as $v2) {
					$match = Nette\Utils\Strings::match($v2->getFilename(), '~^(?P<domain>.*?)\.(?P<locale>[^\.]+)\.(?P<format>[^\.]+)$~');

					if (!$match) {
						continue;
					}

					if ($whitelistRegexp !== null && !preg_match($whitelistRegexp, $match['locale'])) {
						if (isset($tracyPanel)) {
							$tracyPanel->addSetup('addIgnoredResource', [$match['format'], $v2->getPathname(), $match['locale'], $match['domain']]);
						}

						if (!$config['debug']) {
							continue;// ignore in production mode, there is no need to pass the ignored resources
						}
					}

					$translator->addSetup('addResource', [$match['format'], $v2->getPathname(), $match['locale'], $match['domain']]);

					if (isset($tracyPanel)) {
						$tracyPanel->addSetup('addResource', [$match['format'], $v2->getPathname(), $match['locale'], $match['domain']]);
					}
				}
			}
		}
	}


	/**
	 * @param Nette\PhpGenerator\ClassType $class
	 */
	public function afterCompile(Nette\PhpGenerator\ClassType $class): void
	{
		$config = $this->validateConfig($this->defaults, $this->config);

		if ($config['debug'] && $config['debugger']) {
			$initialize = $class->getMethod('initialize');
			$initialize->addBody('$this->getService(?)->addPanel($this->getService(?));', ['tracy.bar', $this->prefix('tracyPanel')]);
		}
	}
}
