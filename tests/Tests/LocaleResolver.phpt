<?php declare(strict_types = 1);

namespace Tests;

use Contributte;
use Mockery;
use Nette;
use Tester;
use Tests;

$container = require __DIR__ . '/../bootstrap.php';

class LocaleResolver extends Tests\TestAbstract
{

	public function test01(): void
	{
		$containerMock = Mockery::mock(Nette\DI\Container::class);
		$localeResolver = new Contributte\Translation\LocaleResolver($containerMock);

		$localeResolver->addResolver('resolver1');

		Tester\Assert::count(1, $localeResolver->resolvers);

		$localeResolver->addResolver('resolver2');

		Tester\Assert::count(2, $localeResolver->resolvers);
	}

}

(new LocaleResolver($container))->run();
