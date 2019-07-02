<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Tests;

use Contributte;
use Mockery;
use Nette;
use Tester;
use Tests;

$container = require __DIR__ . '/../bootstrap.php';

class DebuggerTranslator extends Tests\TestAbstract
{

	public function test01(): void
	{
		$debuggerTranslator = new Contributte\Translation\DebuggerTranslator(new Contributte\Translation\LocaleResolver(Mockery::mock(Nette\DI\Container::class)), new Contributte\Translation\FallbackResolver(), 'en', __DIR__ . '/cacheDir', true);

		Tester\Assert::null($debuggerTranslator->tracyPanel);

		new Contributte\Translation\Tracy\Panel($debuggerTranslator);

		Tester\Assert::true($debuggerTranslator->tracyPanel instanceof Contributte\Translation\Tracy\Panel);
	}

}


(new DebuggerTranslator($container))->run();
