<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

$container = require __DIR__ . '/../bootstrap.php';

class PrefixedTranslator extends AbstractTest
{

	public function test01(): void
	{
		$translator = Mockery::mock(Contributte\Translation\Translator::class);
		$prefixedTranslator = new Contributte\Translation\PrefixedTranslator($translator, 'prefix');

		Tester\Assert::true($prefixedTranslator->translator instanceof Contributte\Translation\Translator);
		Tester\Assert::same('prefix', $prefixedTranslator->prefix);
	}

}


(new PrefixedTranslator($container))->run();
