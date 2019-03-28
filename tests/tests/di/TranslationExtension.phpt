<?php

/**
 * This file is part of the Translette/Translation
 */

declare(strict_types=1);

namespace Translette\Translation\Tests\Tests\DI;

use Nette;
use Tester;
use Translette;

$container = require __DIR__ . '/../../bootstrap.php';


/**
 * @author Ales Wita
 */
class TranslationExtension extends Translette\Translation\Tests\AbstractTest
{
	public function test01(): void
	{
		Tester\Assert::exception(function (): void {$this->createContainer(['localeResolvers' => ['\\stdClass']]);}, Translette\Translation\InvalidArgumentException::class, 'Resolver must implement interface "Translette\\Translation\\LocalesResolvers\\ResolverInterface".');
		Tester\Assert::exception(function (): void {$this->createContainer(['cache' => ['factory' => '\\stdClass']]);}, Translette\Translation\InvalidArgumentException::class, 'Cache factory must implement interface "Symfony\Component\Config\ConfigCacheFactoryInterface".');
		Tester\Assert::exception(function (): void {$this->createContainer([]);}, Translette\Translation\InvalidArgumentException::class, 'Default locale must be set.');
		Tester\Assert::exception(function (): void {$this->createContainer(['locales' => ['default' => 'en'], 'loaders' => ['\\stdClass']]);}, Translette\Translation\InvalidArgumentException::class, 'Loader must implement interface "Symfony\Component\Translation\Loader\LoaderInterface".');
		Tester\Assert::exception(function (): void {$this->createContainer(['locales' => ['default' => 'en'], 'dirs' => [__DIR__ . '/__no_exists__']]);}, \UnexpectedValueException::class);
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
			$compiler->addExtension('translation', new Translette\Translation\DI\TranslationExtension);
			$compiler->addConfig(['parameters' => $this->container->getParameters(), 'translation' => $config]);
		});

		return new $class;
	}
}


(new TranslationExtension($container))->run();
