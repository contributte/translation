<?php

/**
 * This file is part of the Translette\Translation
 */

declare(strict_types=1);

namespace Translette\Translation\Tests\Tests\LocalesResolvers;

use Nette;
use Tester;
use Translette;

$container = require __DIR__ . '/../../bootstrap.phpt';


/**
 * @author Ales Wita
 */
class Header extends Tester\TestCase
{
	/** @var Nette\DI\Container */
	private $container;


	/**
	 * @param Nette\DI\Container $container
	 */
	public function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
	}


	public function test01(): void
	{
		$requestMock = \Mockery::mock(Nette\Http\Request::class);
		$translatorMock = \Mockery::mock(Translette\Translation\Translator::class);
		$headerResolver = new Translette\Translation\LocalesResolvers\Header($requestMock);

		$requestMock->shouldReceive('detectLanguage')
			->once()
			->withArgs([['en']])
			->andReturn(null);

		$translatorMock->shouldReceive('getAvailableLocales')
			->once()
			->withNoArgs()
			->andReturn(['en']);

		Tester\Assert::null($headerResolver->resolve($translatorMock));


		$translatorMock->shouldReceive('getAvailableLocales')
			->once()
			->withNoArgs()
			->andReturn(['en']);

		$requestMock->shouldReceive('detectLanguage')
			->once()
			->withArgs([['en']])
			->andReturn('en');

		Tester\Assert::same('en', $headerResolver->resolve($translatorMock));
	}
}


$test = new Header($container);
$test->run();
