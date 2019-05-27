<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Tests\LocalesResolvers;

use Contributte;
use Mockery;
use Nette;
use Tester;
use Tests;

$container = require __DIR__ . '/../../bootstrap.php';

class Session extends Tests\TestAbstract
{

	public function test01(): void
	{
		Tester\Assert::null($this->resolve(null, []));
		Tester\Assert::null($this->resolve('cs', ['en']));
		Tester\Assert::same('en', $this->resolve('en', ['en']));
		Tester\Assert::same('en', $this->resolve('en', ['en_US']));
		Tester\Assert::same('cs', $this->resolve('cs', ['en_US', 'cs_CZ']));
		Tester\Assert::error(function (): void {
			$this->resolve(null, [], false, true);
		}, E_USER_WARNING, 'The advice of session locale resolver is required but the session has not been started and headers had been already sent. Either start your sessions earlier or disabled the SessionResolver.');
		Tester\Assert::null(@$this->resolve(null, [], false, true));
	}

	/**
	 * @param string[] $availableLocales
	 * @internal
	 */
	private function resolve(?string $locale, array $availableLocales, bool $sessionIsStarted = true, bool $responseIsSent = false): ?string
	{
		$responseMock = Mockery::mock(Nette\Http\IResponse::class);
		$sessionMock = Mockery::mock(Nette\Http\Session::class);
		$sessionSection = new Nette\Http\SessionSection($sessionMock, Contributte\Translation\LocalesResolvers\Session::class);

		$sessionMock->shouldReceive('getSection')
			->once()
			->withArgs([Contributte\Translation\LocalesResolvers\Session::class])
			->andReturn($sessionSection);

		$resolver = new Contributte\Translation\LocalesResolvers\Session($responseMock, $sessionMock);
		$translatorMock = Mockery::mock(Contributte\Translation\Translator::class);

		$translatorMock->shouldReceive('getAvailableLocales')
			->once()
			->withNoArgs()
			->andReturn($availableLocales);

		$sessionMock->shouldReceive('isStarted')
			->once()
			->withNoArgs()
			->andReturn($sessionIsStarted);

		$responseMock->shouldReceive('isSent')
			->once()
			->withNoArgs()
			->andReturn($responseIsSent);

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
