<?php declare(strict_types = 1);

namespace Tests\LocalesResolvers;

use Contributte\Translation\FallbackResolver;
use Contributte\Translation\LocaleResolver;
use Contributte\Translation\LocalesResolvers\Session;
use Contributte\Translation\Translator;
use Mockery;
use Nette\Http\IResponse;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Http\Session as NetteSession;
use Nette\Http\UrlScript;
use Tester\Assert;
use Tests\TestAbstract;

$container = require __DIR__ . '/../../bootstrap.php';

final class SessionTest extends TestAbstract
{

	protected function tearDown(): void
	{
		// Each test must start without an active native session
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}
	}

	public function test01(): void
	{
		Assert::null($this->resolve(null));
		Assert::same('cs', $this->resolve('cs'));
		Assert::same('en', $this->resolve('en'));
		Assert::null($this->resolve(null));
	}

	public function testNoSessionAndSentHeaders(): void
	{
		$request = new Request(
			new UrlScript('https://example.com')
		);

		$session = new NetteSession(
			$request,
			new Response()
		);

		$response = Mockery::mock(IResponse::class);
		$response->shouldReceive('isSent')->andReturn(true);

		$resolver = new Session(
			$response,
			$session
		);

		$translator = new Translator(
			new LocaleResolver($this->container),
			new FallbackResolver(),
			'en'
		);

		Assert::noError(function () use ($resolver, $translator): void {
			Assert::null($resolver->resolve($translator));
		});

		Assert::false($session->isStarted());
	}

	public function testSessionCookieAndSentHeaders(): void
	{
		$request = new Request(
			new UrlScript('https://example.com'),
			[],
			[],
			[session_name() => 'abc']
		);

		$session = new NetteSession(
			$request,
			new Response()
		);

		$response = Mockery::mock(IResponse::class);
		$response->shouldReceive('isSent')->andReturn(true);

		$resolver = new Session(
			$response,
			$session
		);

		$translator = new Translator(
			new LocaleResolver($this->container),
			new FallbackResolver(),
			'en'
		);

		Assert::error(function () use ($resolver, $translator): void {
			Assert::null($resolver->resolve($translator));
		}, E_USER_WARNING, 'The advice of session locale resolver is required but the session has not been started and headers had been already sent. Either start your sessions earlier or disable the SessionResolver.');

		Assert::false($session->isStarted());
	}

	private function resolve(
		?string $locale
	): ?string
	{
		$response = new Response();

		$request = new Request(
			new UrlScript('https://example.com')
		);

		$session = new NetteSession(
			$request,
			$response
		);

		$resolver = new Session(
			$response,
			$session
		);

		$translator = new Translator(
			new LocaleResolver($this->container),
			new FallbackResolver(),
			'en'
		);

		$resolver->setLocale($locale);

		return $resolver->resolve($translator);
	}

}

(new SessionTest($container))->run();
