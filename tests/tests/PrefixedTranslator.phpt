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
class PrefixedTranslator extends Translette\Translation\Tests\AbstractTest
{
	public function test01(): void
	{
		$translator = \Mockery::mock(Translette\Translation\Translator::class);
		$prefixedTranslator = new Translette\Translation\PrefixedTranslator($translator, 'prefix');

		Tester\Assert::true($prefixedTranslator->translator instanceof Translette\Translation\Translator);
		Tester\Assert::same('prefix', $prefixedTranslator->prefix);
	}
}


(new PrefixedTranslator($container))->run();
