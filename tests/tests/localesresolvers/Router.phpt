<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

$container = require __DIR__ . '/../../bootstrap.php';

class Router extends AbstractTest
{

	public function test01(): void
	{
		Tester\Assert::null($this->resolve(null));
		Tester\Assert::same('', $this->resolve(''));
		Tester\Assert::same('en', $this->resolve('en'));
		Tester\Assert::same('cs', $this->resolve('cs'));
	}

	/**
	 * @internal
	 */
	private function resolve(?string $locale): ?string
	{
		$request = new Nette\Http\Request(new Nette\Http\UrlScript);
		$routeListMock = Mockery::mock(Nette\Application\Routers\RouteList::class);

		$routeListMock->shouldReceive('match')
			->withArgs([$request])
			->once()
			->andReturn([Contributte\Translation\LocalesResolvers\Parameter::$parameter => $locale]);

		$resolver = new Contributte\Translation\LocalesResolvers\Router($request, $routeListMock);
		$translatorMock = Mockery::mock(Contributte\Translation\Translator::class);

		return $resolver->resolve($translatorMock);
	}

}


(new Router($container))->run();
