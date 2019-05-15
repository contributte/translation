<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation\Tests\Tests\DI;

use Contributte;
use Nette;
use Tester;

$container = require __DIR__ . '/../../bootstrap.php';


/**
 * @author Ales Wita
 */
class TranslationExtension extends Contributte\Translation\Tests\AbstractTest
{
	public function test01(): void
	{
		Tester\Assert::exception(function (): void {$this->createContainer(['localeResolvers' => ['\\stdClass']]);}, Contributte\Translation\InvalidArgumentException::class, 'Resolver must implement interface "Contributte\\Translation\\LocalesResolvers\\ResolverInterface".');
		Tester\Assert::exception(function (): void {$this->createContainer(['cache' => ['factory' => '\\stdClass']]);}, Contributte\Translation\InvalidArgumentException::class, 'Cache factory must implement interface "Symfony\Component\Config\ConfigCacheFactoryInterface".');
		Tester\Assert::exception(function (): void {$this->createContainer([]);}, Contributte\Translation\InvalidArgumentException::class, 'Default locale must be set.');
		Tester\Assert::exception(function (): void {$this->createContainer(['locales' => ['default' => 'en'], 'loaders' => ['\\stdClass']]);}, Contributte\Translation\InvalidArgumentException::class, 'Loader must implement interface "Symfony\Component\Translation\Loader\LoaderInterface".');
		Tester\Assert::exception(function (): void {$this->createContainer(['locales' => ['default' => 'en'], 'dirs' => [__DIR__ . '/__no_exists__']]);}, \UnexpectedValueException::class);
	}


	public function test02(): void
	{
		try {
			$loader = new Nette\DI\ContainerLoader($this->container->getParameters()['tempDir'], true);

			$loader->load(function (Nette\DI\Compiler $compiler): void {
				$compiler->addExtension('translation', new Contributte\Translation\DI\TranslationExtension);
				$compiler->addExtension('translationProvider', new class extends Nette\DI\CompilerExtension implements Contributte\Translation\DI\TranslationProviderInterface {
					public function getTranslationResources(): array
					{
						return [__DIR__ . '/__translation_provider_dir__'];
					}
				});
				$compiler->addConfig(['parameters' => $this->container->getParameters(), 'translation' => ['locales' => ['default' => 'en'], 'dirs' => [__DIR__ . '__config_dir__']]]);
			});

		} catch (\UnexpectedValueException $e) {
			Tester\Assert::true(Nette\Utils\Strings::contains($e->getMessage(), __DIR__ . '/__translation_provider_dir__'));// translation provider dirs first !!
		}
	}


	/**
	 * @internal
	 *
	 * @param array $config
	 */
	private function createContainer(array $config)
	{
		$loader = new Nette\DI\ContainerLoader($this->container->getParameters()['tempDir'], true);

		$class = $loader->load(function (Nette\DI\Compiler $compiler) use ($config): void {
			$compiler->addExtension('translation', new Contributte\Translation\DI\TranslationExtension);
			$compiler->addConfig(['parameters' => $this->container->getParameters(), 'translation' => $config]);
		});

		return new $class;
	}
}


(new TranslationExtension($container))->run();
