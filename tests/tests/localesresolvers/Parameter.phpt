<?php

/**
 * This file is part of the Translette/Translation
 */

declare(strict_types=1);

namespace Translette\Translation\Tests\Tests\LocalesResolvers;

use Nette;
use Tester;
use Translette;

$container = require __DIR__ . '/../../bootstrap.php';


/**
 * @author Ales Wita
 */
class Parameter extends Translette\Translation\Tests\AbstractTest
{
	public function test01(): void
	{
		Tester\Assert::null($this->resolve(null));
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
		$applicationMock = \Mockery::mock(Nette\Application\Application::class);

		$applicationMock->shouldReceive('getRequests')
			->once()
			->withNoArgs()
			->andReturn([new Nette\Application\Request('presenter', 'GET', ['action' => 'default', Translette\Translation\LocalesResolvers\Parameter::$localeParameter => $locale])]);

		$resolver = new Translette\Translation\LocalesResolvers\Parameter($applicationMock);
		$translatorMock = \Mockery::mock(Translette\Translation\Translator::class);

		return $resolver->resolve($translatorMock);
	}


	public function test02(): void
	{
		$applicationMock = \Mockery::mock(Nette\Application\Application::class);

		$applicationMock->shouldReceive('getRequests')
			->once()
			->withNoArgs()
			->andReturn([null]);

		$resolver = new Translette\Translation\LocalesResolvers\Parameter($applicationMock);
		$translatorMock = \Mockery::mock(Translette\Translation\Translator::class);

		Tester\Assert::null($resolver->resolve($translatorMock));
	}
}


(new Parameter($container))->run();
