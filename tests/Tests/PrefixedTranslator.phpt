<?php declare(strict_types = 1);

namespace Tests;

use Contributte;
use Mockery;
use Tester\Assert;
use Tests;

$container = require __DIR__ . '/../bootstrap.php';

class PrefixedTranslator extends Tests\TestAbstract
{

	public function test01(): void
	{
		$translator = Mockery::mock(Contributte\Translation\Translator::class);
		$prefixedTranslator = new Contributte\Translation\PrefixedTranslator($translator, 'prefix');

		Assert::true($prefixedTranslator->getTranslator() instanceof Contributte\Translation\Translator);
		Assert::same('prefix', $prefixedTranslator->getPrefix());
	}

}

(new PrefixedTranslator($container))->run();
