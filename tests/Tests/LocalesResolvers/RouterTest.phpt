<?php declare(strict_types = 1);

namespace Tests\LocalesResolvers;

use Contributte\Translation\LocalesResolvers\Router;
use Contributte\Translation\Translator;
use Mockery;
use Nette\Http\Request;
use Nette\Http\UrlScript;
use Nette\Routing\RouteList;
use Tester\Assert;
use Tests\TestAbstract;

$container = require __DIR__ . '/../../bootstrap.php';

final class RouterTest extends TestAbstract
{

	public function test01(): void
	{
		Assert::null($this->resolve(null));
		Assert::same('', $this->resolve(''));
		Assert::same('en', $this->resolve('en'));
		Assert::same('cs', $this->resolve('cs'));
	}

	private function resolve(
		?string $locale
	): ?string
	{
		$request = new Request(new UrlScript());
		$routeListMock = Mockery::mock(RouteList::class);

		$routeListMock->shouldReceive('match')
			->withArgs([$request])
			->once()
			->andReturn($locale !== null ? [Router::$parameter => $locale] : []);

		$resolver = new Router($request, $routeListMock);
		$translatorMock = Mockery::mock(Translator::class);

		return $resolver->resolve($translatorMock);
	}

}

(new RouterTest($container))->run();
