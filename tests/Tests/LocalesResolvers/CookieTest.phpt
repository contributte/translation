<?php declare(strict_types = 1);

namespace Tests\LocalesResolvers;

use Contributte\Translation\FallbackResolver;
use Contributte\Translation\LocaleResolver;
use Contributte\Translation\LocalesResolvers\Cookie;
use Contributte\Translation\Translator;
use Mockery;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Tester\Assert;
use Tests\TestAbstract;

$container = require __DIR__ . '/../../bootstrap.php';

final class CookieTest extends TestAbstract
{

	private static array $cookies;

	public function test01(): void
	{
		self::$cookies = [];

		Assert::null($this->resolve(null));
		Assert::same('cs', $this->resolve('cs'));
		Assert::same('en', $this->resolve('en'));
		Assert::null($this->resolve(null));
	}

	private function resolve(
		?string $locale
	): ?string
	{
		$response = Mockery::mock(IResponse::class);
		$response->shouldReceive('setCookie')->andReturnUsing(function ($name, $value) {
			self::$cookies[$name] = $value;
			self::$cookies = array_filter(self::$cookies);
		});
		$response->shouldReceive('deleteCookie')->andReturnUsing(function ($name) {
			unset(self::$cookies[$name]);
		});

		$request = Mockery::mock(IRequest::class);
		$request->shouldReceive('getCookie')->andReturnUsing(function ($name) {
			return self::$cookies[$name] ?? null;
		});

		$resolver = new Cookie(
			$request,
			$response,
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

(new CookieTest($container))->run();
