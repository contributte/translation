<?php declare(strict_types = 1);

namespace Tests\DI;

use Contributte\Translation\Tracy\Panel;
use Contributte\Translation\Translator;
use Nette\Localization\ITranslator;
use Nette\Utils\Strings;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tester\Assert;
use Tests\Helpers;
use Tests\TestAbstract;

$container = require __DIR__ . '/../../bootstrap.php';

final class TranslationExtensionTest4 extends TestAbstract
{

	public function test01(): void
	{
		$container = Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
			'translation' => [
				'locales' => ['whitelist' => ['en']],
			],
		]);

		/** @var Panel $panel */
		$panel = $container->getByType(Panel::class);

		/** @var Translator $translator */
		$translator = $container->getByType(ITranslator::class);

		$tracyPanel = $translator->getTracyPanel();

		Assert::count(1, $tracyPanel->getResources());
		Assert::count(1, $panel->getResources());
		Assert::count(1, $tracyPanel->getIgnoredResources());
		Assert::count(1, $panel->getIgnoredResources());

		$foo = $tracyPanel->getIgnoredResources();
		$foo = end($foo);
		Assert::same('messages', end($foo));
		Assert::true(Strings::contains(key($foo), 'messages.cs_CZ.neon'));

		$foo = $panel->getIgnoredResources();
		$foo = end($foo);
		Assert::same('messages', end($foo));
		Assert::true(Strings::contains(key($foo), 'messages.cs_CZ.neon'));

		$symfonyTranslator = $container->getByType(TranslatorInterface::class);
		Assert::same($translator, $symfonyTranslator);

		$contributteTranslator = $container->getByType(Translator::class);
		Assert::same($translator, $contributteTranslator);
	}

}

(new TranslationExtensionTest4($container))->run();
