<?php declare(strict_types = 1);

namespace Tests\LocalesResolvers;

use Contributte;
use Mockery;
use Nette;
use Tester\Assert;
use Tests;

$container = require __DIR__ . '/../../bootstrap.php';

class Parameter extends Tests\TestAbstract
{

	public function test01(): void
	{
		Assert::same('', $this->resolve(''));
		Assert::same('en', $this->resolve('en'));
		Assert::same('cs', $this->resolve('cs'));
	}

	/**
	 * @internal
	 */
	private function resolve(?string $locale): ?string
	{
		$request = new Nette\Http\Request(new Nette\Http\UrlScript('https://www.example.com/?' . Contributte\Translation\LocalesResolvers\Parameter::$parameter . '=' . $locale));

		$resolver = new Contributte\Translation\LocalesResolvers\Parameter($request);
		$translatorMock = Mockery::mock(Contributte\Translation\Translator::class);

		return $resolver->resolve($translatorMock);
	}

	public function test02(): void
	{
		$request = new Nette\Http\Request(new Nette\Http\UrlScript('https://www.example.com'));

		$resolver = new Contributte\Translation\LocalesResolvers\Parameter($request);
		$translatorMock = Mockery::mock(Contributte\Translation\Translator::class);

		Assert::null($resolver->resolve($translatorMock));
	}

}

(new Parameter($container))->run();
