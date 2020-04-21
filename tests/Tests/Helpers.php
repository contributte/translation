<?php declare(strict_types = 1);

namespace Tests;

use Contributte;
use Nette;

class Helpers
{

	use Nette\StaticClass;

	/**
	 * @param mixed[] $config
	 */
	public static function createContainerFromConfigurator(string $tempDir, array $config = []): Nette\DI\Container
	{
		$config = array_merge_recursive($config, [
			'extensions' => [
				'translation' => Contributte\Translation\DI\TranslationExtension::class,
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

		$configurator = new Nette\Configurator();

		$configurator->setTempDirectory($tempDir)
			->addConfig($config);

		return $configurator->createContainer();
	}

}
