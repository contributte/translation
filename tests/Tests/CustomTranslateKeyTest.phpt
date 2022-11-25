<?php declare(strict_types = 1);

namespace Tests;

use Contributte\Translation\FallbackResolver;
use Contributte\Translation\LocaleResolver;
use Contributte\Translation\Translator;
use Contributte\Translation\Wrappers\Message;
use Generator;
use Mockery;
use Nette\DI\Container;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';

final class CustomTranslateKeyTest extends TestAbstract
{

	public function test01(): void
	{
		$translations = [
			'hi' => 'Hi :name!',
		];

		$translator = new Translator(new LocaleResolver(Mockery::mock(Container::class)), new FallbackResolver(), 'en', $this->container->getParameters()['tempDir'], true);
		$translator->addLoader('array', new ArrayLoader());
		$translator->addResource('array', $translations, 'en');

		Assert::same('Hi :name!', $translator->translate('hi', ['name' => 'Ales']));
		Assert::same('Hi :name!', $translator->translate(new Message('hi', ['name' => 'Ales'])));

		$translator->setTranslateKeyGenerator(function (array $params): Generator {
			foreach ($params as $k1 => $v1) {
				yield ':' . $k1 => $v1;
			}
		});

		Assert::same('Hi Ales!', $translator->translate('hi', ['name' => 'Ales']));
		Assert::same('Hi Ales!', $translator->translate(new Message('hi', ['name' => 'Ales'])));
	}

}

(new CustomTranslateKeyTest($container))->run();
