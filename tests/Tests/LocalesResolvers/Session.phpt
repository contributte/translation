<?php declare(strict_types = 1);

namespace Tests\LocalesResolvers;

use Contributte;
use Mockery;
use Nette;
use Tester\Assert;
use Tests;

$container = require __DIR__ . '/../../bootstrap.php';

class Session extends Tests\TestAbstract
{

	public function test01(): void
	{
		Assert::null($this->resolve(null));
		Assert::same('cs', $this->resolve('cs'));
		Assert::same('en', $this->resolve('en'));
		Assert::error(function (): void {
			$this->resolve(null, false, true);
		}, E_USER_WARNING, 'The advice of session locale resolver is required but the session has not been started and headers had been already sent. Either start your sessions earlier or disable the SessionResolver.');
		Assert::null(@$this->resolve(null, false, true));
	}

	/**
	 * @internal
	 */
	private function resolve(?string $locale, bool $sessionIsStarted = true, bool $responseIsSent = false): ?string
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
