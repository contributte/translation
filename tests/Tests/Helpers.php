<?php declare(strict_types = 1);

namespace Tests;

use Contributte\Translation\DI\TranslationExtension;
use Nette\Bootstrap\Configurator;
use Nette\DI\Container;

final class Helpers
{

	/**
	 * @param array<mixed> $config
	 */
	public static function createContainerFromConfigurator(
		string $tempDir,
		array $config = []
	): Container
	{
		$config = array_merge_recursive($config, [
			'extensions' => [
				'translation' => TranslationExtension::class,
			],
			'translation' => [
				'debug' => true,
				'debugger' => true,
				'localeResolvers' => [
					LocaleResolverMock::class,
				],
				'dirs' => [
					__DIR__ . '/../lang',
					__DIR__ . '/../lang_overloading',
				],
			],
		]);

		$configurator = new Configurator();

		$configurator->setTempDirectory($tempDir)
			->addConfig($config);

		return $configurator->createContainer();
	}

}
