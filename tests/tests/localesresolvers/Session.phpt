<?php

/**
 * This file is part of the Translette/Translation
 */

declare(strict_types=1);

namespace Translette\Translation\Tests\Tests\LocalesResolvers;

use Nette;
use Tester;
use Translette;

$container = require __DIR__ . '/../../bootstrap.php';


/**
 * @author Ales Wita
 */
class Session extends Translette\Translation\Tests\AbstractTest
{
	public function test01(): void
	{
		Tester\Assert::null($this->resolve(null, []));
		Tester\Assert::null($this->resolve('cs', ['en']));
		Tester\Assert::same('en', $this->resolve('en', ['en']));
		Tester\Assert::same('en', $this->resolve('en', ['en_US']));
		Tester\Assert::same('cs', $this->resolve('cs', ['en_US', 'cs_CZ']));
	}


	/**
	 * @internal
	 *
	 * @param string|null $locale
	 * @param array $availableLocales
	 * @return string|null
	 */
	private function resolve(?string $locale, array $availableLocales): ?string
	{
		$responseMock = \Mockery::mock(Nette\Http\Response::class);
		$sessionMock = \Mockery::mock(Nette\Http\Session::class);
		$sessionSection = new Nette\Http\SessionSection($sessionMock, Translette\Translation\LocalesResolvers\Session::class);

		$sessionMock->shouldReceive('getSection')
			->once()
			->withArgs([Translette\Translation\LocalesResolvers\Session::class])
			->andReturn($sessionSection);

		$resolver = new Translette\Translation\LocalesResolvers\Session($responseMock, $sessionMock);
		$translatorMock = \Mockery::mock(Translette\Translation\Translator::class);

		$translatorMock->shouldReceive('getAvailableLocales')
			->once()
			->withNoArgs()
			->andReturn($availableLocales);

		$sessionMock->shouldReceive('isStarted')
			->once()
			->withNoArgs()
			->andReturn(true);

		$responseMock->shouldReceive('isSent')
			->once()
			->withNoArgs()
			->andReturn(true);

		$sessionMock->shouldReceive('start')
			->once()
			->withNoArgs();

		$sessionMock->shouldReceive('exists')
			->once()
			->withNoArgs()
			->andReturn(true);

		$resolver->setLocale($locale);

		return $resolver->resolve($translatorMock);
	}
}


(new Session($container))->run();
