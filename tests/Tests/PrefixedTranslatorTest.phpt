<?php declare(strict_types = 1);

namespace Tests;

use Contributte\Translation\PrefixedTranslator;
use Contributte\Translation\Translator;
use Mockery;
use Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';

final class PrefixedTranslatorTest extends TestAbstract
{

	public function test01(): void
	{
		$translator = Mockery::mock(Translator::class);
		$prefixedTranslator = new PrefixedTranslator($translator, 'prefix');

		Assert::true($prefixedTranslator->getTranslator() instanceof Translator);
		Assert::same('prefix', $prefixedTranslator->getPrefix());
	}

}

(new PrefixedTranslatorTest($container))->run();
