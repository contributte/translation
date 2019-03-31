<?php

/**
 * This file is part of the Translette/Translation
 */

declare(strict_types=1);

namespace Translette\Translation\Tests\Tests;

use Tester;
use Translette;

$container = require __DIR__ . '/../bootstrap.php';


/**
 * @author Ales Wita
 */
class LocaleResolver extends Translette\Translation\Tests\AbstractTest
{
	public function test01(): void
	{
		$resolverMock = \Mockery::mock(Translette\Translation\LocalesResolvers\Parameter::class);
		$localeResolver = new Translette\Translation\LocaleResolver;

		$localeResolver->addResolver($resolverMock);

		Tester\Assert::count(1, $localeResolver->resolvers);

		$localeResolver->addResolver($resolverMock);

		Tester\Assert::count(2, $localeResolver->resolvers);
	}
}


(new LocaleResolver($container))->run();
