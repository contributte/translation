<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation\Tests\Tests;

use Contributte;
use Tester;

$container = require __DIR__ . '/../bootstrap.php';


/**
 * @author Ales Wita
 */
class PrefixedTranslator extends Contributte\Translation\Tests\AbstractTest
{
	public function test01(): void
	{
		$translator = \Mockery::mock(Contributte\Translation\Translator::class);
		$prefixedTranslator = new Contributte\Translation\PrefixedTranslator($translator, 'prefix');

		Tester\Assert::true($prefixedTranslator->translator instanceof Contributte\Translation\Translator);
		Tester\Assert::same('prefix', $prefixedTranslator->prefix);
	}
}


(new PrefixedTranslator($container))->run();
