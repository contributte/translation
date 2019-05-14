<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation\DI;

use Contributte;
use Nette;
use Nette\Schema\Expect;
use Symfony;
use Tracy;


/**
 * @property      \stdClass $config
 *
 * @author Ales Wita
 * @author Filip Prochazka
 */
class TranslationExtension extends Nette\DI\CompilerExtension
{
	/**
	 * @return Nette\Schema\Schema
	 */
	public function getConfigSchema(): Nette\Schema\Schema
	{
		$builder = $this->getContainerBuilder();

		return Expect::structure([
			'debug' => Expect::bool($builder->parameters['debugMode']),
			'debugger' => Expect::bool(interface_exists(Tracy\IBarPanel::class)),
			'locales' => Expect::structure([
				'whitelist' => Expect::array()->default(null),
				'default' => Expect::string(null),
				'fallback' => Expect::array()->default(['en_US']),
			]),
			'localeResolvers' => Expect::array()->default([
				Contributte\Translation\LocalesResolvers\Session::class,
				Contributte\Translation\LocalesResolvers\Router::class,
				Contributte\Translation\LocalesResolvers\Parameter::class,
				Contributte\Translation\LocalesResolvers\Header::class,
			]),
			'loaders' => Expect::array()->default([
				'neon' => Contributte\Translation\Loaders\Neon::class,
			]),
			'dirs' => Expect::array()->default([]),
			'cache' => Expect::structure([
				'dir' => Expect::string($builder->parameters['tempDir'] . '/cache/translation'),
				'factory' => Expect::string(Symfony\Component\Config\ConfigCacheFactory::class),
			]),
		]);
	}


	/**
	 * @throws Contributte\Translation\InvalidArgumentException|\ReflectionException
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		// LocaleResolver
		$localeResolver = $builder->addDefinition($this->prefix('localeResolver'))
			->setFactory(Contributte\Translation\LocaleResolver::class);


		// LocaleResolvers
		$localeResolvers = [];

		foreach ($this->config->localeResolvers as $v1) {
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
		$reflection = new \ReflectionClass($this->config->cache->factory);

		if (!$reflection->implementsInterface(Symfony\Component\Config\ConfigCacheFactoryInterface::class)) {
			throw new Contributte\Translation\InvalidArgumentException('Cache factory must implement interface "' . Symfony\Component\Config\ConfigCacheFactoryInterface::class . '".');
		}

		$configCacheFactory = $builder->addDefinition($this->prefix('configCacheFactory'))
			->setFactory($this->config->cache->factory, [$this->config->debug]);


		// Translator
		if ($this->config->locales->default === null) {
			throw new Contributte\Translation\InvalidArgumentException('Default locale must be set.');
		}

		$translator = $builder->addDefinition($this->prefix('translator'))
			->setType(Nette\Localization\ITranslator::class)
			->setFactory(Contributte\Translation\Translator::class, ['defaultLocale' => $this->config->locales->default, 'cacheDir' => $this->config->cache->dir, 'debug' => $this->config->debug])
			->addSetup('setLocalesWhitelist', [$this->config->locales->whitelist])
			->addSetup('setConfigCacheFactory', [$configCacheFactory])
			->addSetup('setFallbackLocales', [$this->config->locales->fallback]);


		// Loaders
		foreach ($this->config->loaders as $k1 => $v1) {
			$reflection = new \ReflectionClass($v1);

			if (!$reflection->implementsInterface(Symfony\Component\Translation\Loader\LoaderInterface::class)) {
				throw new Contributte\Translation\InvalidArgumentException('Loader must implement interface "' . Symfony\Component\Translation\Loader\LoaderInterface::class . '".');
			}

			$loader = $builder->addDefinition($this->prefix('loader' . Nette\Utils\Strings::firstUpper($k1)))
				->setFactory($v1);

			$translator->addSetup('addLoader', [$k1, $loader]);
		}


		// Tracy\Panel
		if ($this->config->debug && $this->config->debugger) {
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

		/** @var Nette\DI\Definitions\ServiceDefinition $translator */
		$translator = $builder->getDefinition($this->prefix('translator'));
		$whitelistRegexp = Contributte\Translation\Helpers::whitelistRegexp($this->config->locales->whitelist);

		if ($this->config->debug && $this->config->debugger) {
			/** @var Nette\DI\Definitions\ServiceDefinition $tracyPanel */
			$tracyPanel = $builder->getDefinition($this->prefix('tracyPanel'));
		}

		$templateFactoryName = $builder->getByType(Nette\Application\UI\ITemplateFactory::class);

		if ($templateFactoryName !== null) {
			/** @var Nette\DI\Definitions\ServiceDefinition $templateFactory */
			$templateFactory = $builder->getDefinition($templateFactoryName);

			$templateFactory->addSetup('
					$service->onCreate[] = function (Nette\\Bridges\\ApplicationLatte\\Template $template): void {
						$template->setTranslator(?);
					};', [$translator]);
		}

		if ($builder->hasDefinition('latte.latteFactory')) {
			/** @var Nette\DI\Definitions\FactoryDefinition $latteFactory */
			$latteFactory = $builder->getDefinition('latte.latteFactory');

			$latteFactory->getResultDefinition()
				->addSetup('?->onCompile[] = function (Latte\\Engine $engine): void { ?::install($engine->getCompiler()); }', ['@self', new Nette\PhpGenerator\PhpLiteral(Contributte\Translation\Latte\Macros::class)])
				->addSetup('addProvider', ['translator', $builder->getDefinition($this->prefix('translator'))]);
		}

		/** @var Contributte\Translation\DI\TranslationProviderInterface $v1 */
		foreach ($this->compiler->getExtensions(TranslationProviderInterface::class) as $v1) {
			$this->config->dirs = array_merge($v1->getTranslationResources(), $this->config->dirs);
		}

		if (count($this->config->dirs) > 0) {
			foreach ($this->config->loaders as $k1 => $v1) {
				foreach (Nette\Utils\Finder::find('*.' . $k1)->from($this->config->dirs) as $v2) {
					$match = Nette\Utils\Strings::match($v2->getFilename(), '~^(?P<domain>.*?)\.(?P<locale>[^\.]+)\.(?P<format>[^\.]+)$~');

					if (!$match) {
						continue;
					}

					if ($whitelistRegexp !== null && !preg_match($whitelistRegexp, $match['locale'])) {
						if (isset($tracyPanel)) {
							$tracyPanel->addSetup('addIgnoredResource', [$match['format'], $v2->getPathname(), $match['locale'], $match['domain']]);
						}

						if (!$this->config->debug) {
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
		if ($this->config->debug && $this->config->debugger) {
			$initialize = $class->getMethod('initialize');
			$initialize->addBody('$this->getService(?)->addPanel($this->getService(?));', ['tracy.bar', $this->prefix('tracyPanel')]);
		}
	}
}
