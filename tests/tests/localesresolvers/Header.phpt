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
class Header extends Contributte\Translation\Tests\AbstractTest
{
	public function test01(): void
	{
		Tester\Assert::null($this->resolve(null, ['foo']));
		Tester\Assert::null($this->resolve('en', []));
		Tester\Assert::null($this->resolve('foo', ['en']));
		Tester\Assert::same('en', $this->resolve('en, cs', ['en', 'cs']));
		Tester\Assert::same('en', $this->resolve('en, cs', ['cs', 'en']));
		Tester\Assert::same('en-us', $this->resolve('da, en-us;q=0.8, en;q=0.7', ['en', 'en-us']));
		Tester\Assert::same('en', $this->resolve('da, en-us;q=0.8, en;q=0.7', ['en']));
		Tester\Assert::same('en', $this->resolve('da, en_us', ['en']));
		Tester\Assert::same('en-us', $this->resolve('da, en_us', ['en', 'en-us']));
	}


	/**
	 * @internal
	 *
	 * @param string|null $locale
	 * @param array $availableLocales
	 * @return string|null
	 */
	private function resolve(?string $locale, array $availableLocales): ?string
	{
		$request = new Nette\Http\Request(new Nette\Http\UrlScript, null, null, null, ['Accept-Language' => $locale]);
		$resolver = new Contributte\Translation\LocalesResolvers\Header($request);
		$translatorMock = \Mockery::mock(Contributte\Translation\Translator::class);

		$translatorMock->shouldReceive('getAvailableLocales')
			->once()
			->withNoArgs()
			->andReturn($availableLocales);

		return $resolver->resolve($translatorMock);
	}
}


(new Header($container))->run();
