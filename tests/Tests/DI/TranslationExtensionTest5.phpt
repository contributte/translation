<?php declare(strict_types = 1);

namespace Tests\DI;

use Nette\Localization\ITranslator;
use Tester\Assert;
use Tests\CustomTranslatorMock;
use Tests\Helpers;
use Tests\PsrLoggerMock;
use Tests\TestAbstract;

$container = require __DIR__ . '/../../bootstrap.php';

final class TranslationExtensionTest5 extends TestAbstract
{

	public function test01(): void
	{
		$tempDir = Helpers::generateRandomTempDir($this->container->getParameters()['tempDir'] . '/' . self::class);

		$container = Helpers::createContainerFromConfigurator(
			$tempDir,
			[
				'translation' => [
					'logger' => PsrLoggerMock::class,
				],
			]
		);

		Assert::count(1, $container->findByType(PsrLoggerMock::class));

		Helpers::clearTempDir($tempDir);
	}

	public function test02(): void
	{
		$tempDir = Helpers::generateRandomTempDir($this->container->getParameters()['tempDir'] . '/' . self::class);

		$container = Helpers::createContainerFromConfigurator(
			$tempDir,
			[
				'translation' => [
					'locales' => [
						'fallback' => ['cs_CZ'],
					],
				],
			]
		);

		/** @var \Contributte\Translation\Translator $translator */
		$translator = $container->getByType(ITranslator::class);

		Assert::same($translator->getFallbackLocales(), ['cs_CZ']);

		Helpers::clearTempDir($tempDir);
	}

	public function test03(): void
	{
		$tempDir = Helpers::generateRandomTempDir($this->container->getParameters()['tempDir'] . '/' . self::class);

		$container = Helpers::createContainerFromConfigurator(
			$tempDir,
			[
				'translation' => [
					'locales' => ['whitelist' => ['en']],
					'translatorFactory' => CustomTranslatorMock::class,
				],
			]
		);

		/** @var \Contributte\Translation\Translator $translator */
		$translator = $container->getByType(ITranslator::class);

		Assert::type(CustomTranslatorMock::class, $translator);

		$factoryTranslator = $container->getByType(CustomTranslatorMock::class);
		Assert::same($translator, $factoryTranslator);

		Helpers::clearTempDir($tempDir);
	}

}

(new TranslationExtensionTest5($container))->run();
