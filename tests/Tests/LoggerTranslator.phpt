<?php declare(strict_types = 1);

namespace Tests;

use Contributte;
use Mockery;
use Nette;
use Tester;
use Tests;

$container = require __DIR__ . '/../bootstrap.php';

class LoggerTranslator extends Tests\TestAbstract
{

	public function test01(): void
	{
		$loggerTranslator = new Contributte\Translation\LoggerTranslator(new Contributte\Translation\LocaleResolver(Mockery::mock(Nette\DI\Container::class)), new Contributte\Translation\FallbackResolver(), 'en', __DIR__ . '/cacheDir', true);

		Tester\Assert::null($loggerTranslator->psrLogger);

		$loggerTranslator->setPsrLogger(new PsrLoggerMock());

		Tester\Assert::true($loggerTranslator->psrLogger instanceof PsrLoggerMock);
	}

}

(new LoggerTranslator($container))->run();
