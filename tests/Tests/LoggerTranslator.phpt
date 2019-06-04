<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Tests;

use Contributte;
use Latte;
use Nette;
use Psr;
use stdClass;
use Tester;
use Tests;

$container = require __DIR__ . '/../bootstrap.php';

class LoggerTranslator extends Tests\TestAbstract
{

	public function test01(): void
	{
		$loggerTranslator = new Contributte\Translation\LoggerTranslator(new Contributte\Translation\LocaleResolver(), new Contributte\Translation\FallbackResolver(), 'en', __DIR__ . '/cacheDir', true);

		Tester\Assert::null($loggerTranslator->psrLogger);

		$loggerTranslator->setPsrLogger(new PsrLoggerMock());

		Tester\Assert::true($loggerTranslator->psrLogger instanceof PsrLoggerMock);
	}

}


(new LoggerTranslator($container))->run();
