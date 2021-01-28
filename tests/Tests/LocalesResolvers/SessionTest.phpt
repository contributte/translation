<?php declare(strict_types = 1);

namespace Tests\LocalesResolvers;

use Contributte\Translation\LocalesResolvers\Session;
use Contributte\Translation\Translator;
use Mockery;
use Nette\Http\IResponse;
use Nette\Http\Session as NetteSession;
use Nette\Http\SessionSection;
use Tester\Assert;
use Tests\TestAbstract;

$container = require __DIR__ . '/../../bootstrap.php';

final class SessionTest extends TestAbstract
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

	private function resolve(
		?string $locale,
		bool $sessionIsStarted = true,
		bool $responseIsSent = false
	): ?string
	{
		$responseMock = Mockery::mock(IResponse::class);
		$sessionMock = Mockery::mock(NetteSession::class);
		$sessionSection = new SessionSection($sessionMock, Session::class);

		$sessionMock->shouldReceive('getSection')
			->once()
			->withArgs([Session::class])
			->andReturn($sessionSection);

		$resolver = new Session($responseMock, $sessionMock);
		$translatorMock = Mockery::mock(Translator::class);

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

(new SessionTest($container))->run();
