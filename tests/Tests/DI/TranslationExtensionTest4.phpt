<?php declare(strict_types = 1);

namespace Tests\DI;

use Contributte\Translation\Translator;
use Nette\Localization\ITranslator;
use Tester\Assert;
use Tests\CustomTranslatorMock;
use Tests\Helpers;
use Tests\PsrLoggerMock;
use Tests\TestAbstract;

$container = require __DIR__ . '/../../bootstrap.php';

final class TranslationExtensionTest4 extends TestAbstract
{

	public function test01(): void
	{
		$container = Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
			'translation' => [
				'logger' => PsrLoggerMock::class,
			],
		]);

		Assert::count(1, $container->findByType(PsrLoggerMock::class));
	}

	public function test02(): void
	{
		$container = Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
			'translation' => [
				'locales' => [
					'fallback' => ['cs_CZ'],
				],
			],
		]);

		/** @var Translator $translator */
		$translator = $container->getByType(ITranslator::class);

		Assert::same($translator->getFallbackLocales(), ['cs_CZ']);
	}

	public function test03(): void
	{
		$container = Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
			'translation' => [
				'locales' => ['whitelist' => ['en']],
				'translatorFactory' => CustomTranslatorMock::class,
			],
		]);

		/** @var \Contributte\Translation\Translator $translator */
		$translator = $container->getByType(ITranslator::class);

		Assert::type(CustomTranslatorMock::class, $translator);

		$factoryTranslator = $container->getByType(CustomTranslatorMock::class);
		Assert::same($translator, $factoryTranslator);
	}

}

(new TranslationExtensionTest4($container))->run();
