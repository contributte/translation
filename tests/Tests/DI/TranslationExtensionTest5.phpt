<?php declare(strict_types = 1);

namespace Tests\DI;

use Nette\DI\MissingServiceException;
use Nette\Localization\ITranslator;
use Tester\Assert;
use Tests\Helpers;
use Tests\TestAbstract;

$container = require __DIR__ . '/../../bootstrap.php';

final class TranslationExtensionTest5 extends TestAbstract
{

	public function test01(): void
	{
		$container = Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
			'translation' => [
				'returnOriginalMessage' => false,
			],
		]);

		/** @var \Contributte\Translation\Translator $translator */
		$translator = $container->getByType(ITranslator::class);

		Assert::false($translator->returnOriginalMessage);
	}

	public function test02(): void
	{
		$container = Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
			'translation' => [
				'returnOriginalMessage' => false,
			],
		]);

		/** @var \Contributte\Translation\Translator $translator */
		$translator = $container->getByType(ITranslator::class);

		Assert::false($translator->returnOriginalMessage);
	}

	public function test03(): void
	{
		Assert::exception(
			function (): void {
				Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
					'translation' => [
						'autowired' => false,
					],
				]);
			},
			MissingServiceException::class
		);
	}

}

(new TranslationExtensionTest5($container))->run();
