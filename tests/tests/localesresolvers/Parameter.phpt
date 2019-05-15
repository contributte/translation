<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation\Tests\Tests\LocalesResolvers;

use Contributte;
use Nette;
use Tester;

$container = require __DIR__ . '/../../bootstrap.php';


/**
 * @author Ales Wita
 */
class Parameter extends Contributte\Translation\Tests\AbstractTest
{
	public function test01(): void
	{
		Tester\Assert::same('', $this->resolve(''));
		Tester\Assert::same('en', $this->resolve('en'));
		Tester\Assert::same('cs', $this->resolve('cs'));
	}


	/**
	 * @internal
	 *
	 * @param string|null $locale
	 * @return string|null
	 */
	private function resolve(?string $locale): ?string
	{
		$request = new Nette\Http\Request(new Nette\Http\UrlScript('https://www.example.com/?' . Contributte\Translation\LocalesResolvers\Parameter::$parameter . '=' . $locale));

		$resolver = new Contributte\Translation\LocalesResolvers\Parameter($request);
		$translatorMock = \Mockery::mock(Contributte\Translation\Translator::class);

		return $resolver->resolve($translatorMock);
	}


	public function test02(): void
	{
		$request = new Nette\Http\Request(new Nette\Http\UrlScript('https://www.example.com'));

		$resolver = new Contributte\Translation\LocalesResolvers\Parameter($request);
		$translatorMock = \Mockery::mock(Contributte\Translation\Translator::class);

		Tester\Assert::null($resolver->resolve($translatorMock));
	}
}


(new Parameter($container))->run();
