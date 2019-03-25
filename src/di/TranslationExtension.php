<?php

/**
 * This file is part of the Translette\Translation
 */

declare(strict_types=1);

namespace Translette\Translation\DI;

use Nette;
use Symfony;
use Tracy;
use Translette;


/**
 * @author Ales Wita
 * @author Filip Prochazka
 */
class TranslationExtension extends Nette\DI\CompilerExtension
{
	/** @var array */
	public $defaults = [
		'debug' => null,// null is auto detect
		'locales' => [
			'whitelist' => null,
			'default' => null,
			'fallback' => [
				'en_US',
			],
		],
		'resolvers' => [
			Translette\Translation\LocalesResolvers\Session::class,
			Translette\Translation\LocalesResolvers\Parameter::class,
			Translette\Translation\LocalesResolvers\Header::class,
		],
		'loaders' => [
			'neon' => Translette\Translation\Loaders\Neon::class,
		],
		'dirs' => [],
		'cache' => [
			'dir' => null,// null is auto detect
			'namespace' => 'translette',
			'factory' => Symfony\Component\Config\ConfigCacheFactory::class,
		],
	];


	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		if ($config['debug'] === null) {
			$this->defaults['debug'] = $config['debug'] = $builder->parameters['debugMode'];
			//$this->defaults['debug'] = $config['debug'] = false;
		}

		if ($config['cache']['dir'] === null) {
			$this->defaults['cache']['dir'] = $config['cache']['dir'] = $builder->parameters['tempDir'] . '/' . $config['cache']['namespace'];
		}


		// Tracy\Panel
		if ($config['debug']) {
			$panel = $builder->addDefinition($this->prefix('panel'))
				->setType(Tracy\IBarPanel::class)
				->setFactory(Translette\Translation\Tracy\Panel::class)
				->setAutowired(false);
		}


		// LocaleResolver
		$localeResolver = $builder->addDefinition($this->prefix('localeResolver'))
			->setFactory(Translette\Translation\LocaleResolver::class)
			->setAutowired(false);


		// Resolvers
		foreach ($config['resolvers'] as $v1) {
			$reflection = new \ReflectionClass($v1);

			if (!$reflection->implementsInterface(Translette\Translation\LocalesResolvers\ResolverInterface::class)) {
				throw new Translette\Translation\InvalidArgumentException('Resolver must implement interface "' . Translette\Translation\LocalesResolvers\ResolverInterface::class . '".');
			}

			$resolver = $builder->addDefinition($this->prefix('resolver.' . Nette\Utils\Strings::lower($reflection->getShortName())))
				->setFactory($v1);

			$localeResolver->addSetup('addResolver', [$resolver]);
		}


		// ConfigCacheFactory
		$reflection = new \ReflectionClass($config['cache']['factory']);

		if (!$reflection->implementsInterface(Symfony\Component\Config\ConfigCacheFactoryInterface::class)) {
			throw new Translette\Translation\InvalidArgumentException('Cache factory must implement interface "' . Symfony\Component\Config\ConfigCacheFactoryInterface::class . '".');
		}

		$configCacheFactory = $builder->addDefinition($this->prefix('configCacheFactory'))
			->setType(Symfony\Component\Config\ConfigCacheFactoryInterface::class)
			->setFactory($config['cache']['factory'], [$config['debug']])
			->setAutowired(false);


		// Translator
		if ($config['locales']['default'] === null) {
			throw new Translette\Translation\InvalidArgumentException('Default locale must be set.');
		}

		$translator = $builder->addDefinition($this->prefix('translator'))
			->setType(Nette\Localization\ITranslator::class)
			->setFactory(Translette\Translation\Translator::class, [$localeResolver, $config['locales']['default'], $config['cache']['dir'], $config['debug']])
			->setAutowired(true)
			->addSetup('setLocalesWhitelist', [$config['locales']['whitelist']])
			->addSetup('setConfigCacheFactory', [$configCacheFactory])
			->addSetup('setFallbackLocales', [$config['locales']['fallback']]);

		if (isset($panel)) {
			$panel->addSetup('setLocalesWhitelist', [$config['locales']['whitelist']]);
		}


		// Loaders
		foreach ($config['loaders'] as $k1 => $v1) {
			$reflection = new \ReflectionClass($v1);

			if (!$reflection->implementsInterface(Symfony\Component\Translation\Loader\LoaderInterface::class)) {
				throw new Translette\Translation\InvalidArgumentException('Loader must implement interface "' . Symfony\Component\Translation\Loader\LoaderInterface::class . '".');
			}

			$loader = $builder->addDefinition($this->prefix('loader.' . $k1))
				->setFactory($v1)
				->setAutowired(false);

			$translator->addSetup('addLoader', [$k1, $loader]);
		}
	}


	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		$translator = $builder->getDefinition($this->prefix('translator'));
		$whitelistRegexp = Translette\Translation\Helpers::whitelistRegexp($config['locales']['whitelist']);

		$templateFactoryName = $builder->getByType(Nette\Application\UI\ITemplateFactory::class);

		if ($templateFactoryName !== null) {
			$builder->getDefinition($templateFactoryName)
				->addSetup('
					$service->onCreate[] = function (Nette\\Bridges\\ApplicationLatte\\Template $template): void {
						$template->setTranslator(?);
					};', [$translator]);
		}

		if ($config['debug']) {
			$panel = $builder->getDefinition($this->prefix('panel'));
		}

		foreach ($config['loaders'] as $k1 => $v1) {
			foreach (Nette\Utils\Finder::find('*.' . $k1)->from($config['dirs']) as $v2) {
				$match = Nette\Utils\Strings::match($v2->getFilename(), '~^(?P<domain>.*?)\.(?P<locale>[^\.]+)\.(?P<format>[^\.]+)$~');

				if (!$match) {
					continue;
				}

				if ($whitelistRegexp !== null && !preg_match($whitelistRegexp, $match['locale'])) {
					if (isset($panel)) {
						$panel->addSetup('addIgnoredResource', [$match['format'], $v2->getPathname(), $match['locale'], $match['domain']]);
					}

					if (!$config['debug']) {
						continue;// ignore in production mode, there is no need to pass the ignored resources
					}
				}

				/*$loaderDefinition = $builder->getDefinition($this->prefix('loader.' . $k1));
				$reflection = new \ReflectionClass($loaderDefinition->getEntity() ?: $loaderDefinition->getType());

				$method = $reflection->getConstructor();

				if ($method !== NULL && $method->getNumberOfRequiredParameters() > 1) {
					return;
				}

				$loader = $reflection->newInstance();

				if (!$loader instanceof Symfony\Component\Translation\Loader\LoaderInterface) {
					return;
				}

				$loader->load($v2->getPathname(), $match['locale'], $match['domain']);*/
				$translator->addSetup('addResource', [$match['format'], $v2->getPathname(), $match['locale'], $match['domain']]);

				if (isset($panel)) {
					$panel->addSetup('addResource', [$match['format'], $v2->getPathname(), $match['locale'], $match['domain']]);
				}
			}
		}



		/*$application = $builder->getByType(Nette\Application\Application::class);

		if ($application !== null) {
			$builder->getDefinition($application)
				->addSetup('$service->onRequest[] = function (Nette\Application\Application $application, Nette\Application\Request $request): void {
					$params = $request->getParameters();

					if ($request->getMethod() === Nette\Application\Request::FORWARD && empty($params[Translette\Translette\Resolvers\Parameter::$localeParameter])) {
						return;
					}

					$this->request = $request;

					if (!$this->translator) {
						return;
					}

					$this->translator->setLocale(null);
					$this->translator->getLocale();// invoke resolver', [[$this->prefix('@userLocaleResolver.param'), 'onRequest']]);

		}*/

		/*
				$applicationService = $builder->getByType(Nette\Application\Application::class) ?: 'application';
				if ($builder->hasDefinition($applicationService)) {
					$builder->getDefinition($applicationService)
						->addSetup('$service->onRequest[] = ?', [[$this->prefix('@userLocaleResolver.param'), 'onRequest']]);

					if ($config['debugger'] && interface_exists(IBarPanel::class)) {
						$builder->getDefinition($applicationService)
							->addSetup('$self = $this; $service->onStartup[] = function () use ($self) { $self->getService(?); }', [$this->prefix('default')])
							->addSetup('$service->onRequest[] = ?', [[$this->prefix('@panel'), 'onRequest']]);
					}
				}
		*/
	}


	/**
	 * @param Nette\PhpGenerator\ClassType $class
	 */
	public function afterCompile(Nette\PhpGenerator\ClassType $class): void
	{
		$config = $this->validateConfig($this->defaults);

		if ($config['debug']) {
			$initialize = $class->getMethod('initialize');
			$initialize->addBody('$this->getService(?)->addPanel($this->getService(?));', ['tracy.bar', $this->prefix('panel')]);
		}
	}
}
