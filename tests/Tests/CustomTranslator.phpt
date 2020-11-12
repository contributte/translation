<?php declare(strict_types = 1);

namespace Tests;

use Contributte;
use Mockery;
use Nette;
use Tester;
use Tests;

$container = require __DIR__ . '/../bootstrap.php';

class CustomTranslator extends Tests\TestAbstract
{

	public function test01(): void
	{
		$translator = new CustomTranslatorMock(new Contributte\Translation\LocaleResolver(Mockery::mock(Nette\DI\Container::class)), new Contributte\Translation\FallbackResolver(), 'en', __DIR__ . '/cacheDir', true);

		Tester\Assert::type(Contributte\Translation\Translator::class, $translator);
	}

}

(new CustomTranslator($container))->run();
