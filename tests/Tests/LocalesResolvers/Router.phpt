<?php declare(strict_types = 1);

namespace Tests\LocalesResolvers;

use Contributte;
use Mockery;
use Nette;
use Tester\Assert;
use Tests;

$container = require __DIR__ . '/../../bootstrap.php';

class Router extends Tests\TestAbstract
{

	public function test01(): void
	{
		Assert::null($this->resolve(null));
		Assert::same('', $this->resolve(''));
		Assert::same('en', $this->resolve('en'));
		Assert::same('cs', $this->resolve('cs'));
	}

	/**
	 * @internal
	 */
	private function resolve(?string $locale): ?string
	{
		$request = new Nette\Http\Request(new Nette\Http\UrlScript());
		$routeListMock = Mockery::mock(Nette\Routing\RouteList::class);

		$routeListMock->shouldReceive('match')
			->withArgs([$request])
			->once()
			->andReturn($locale !== null ? [Contributte\Translation\LocalesResolvers\Parameter::$parameter => $locale] : []);

		$resolver = new Contributte\Translation\LocalesResolvers\Router($request, $routeListMock);
		$translatorMock = Mockery::mock(Contributte\Translation\Translator::class);

		return $resolver->resolve($translatorMock);
	}

}

(new Router($container))->run();
