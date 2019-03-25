<?php

/**
 * This file is part of the Translette\Translation
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
class Parameter extends Tester\TestCase
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
		$applicationMock = \Mockery::mock(Nette\Application\Application::class);
		$translatorMock = \Mockery::mock(Translette\Translation\Translator::class);

		$applicationMock->shouldReceive('getRequests')
			->once()
			->withNoArgs()
			->andReturn([]);

		$parameterResolver = new Translette\Translation\LocalesResolvers\Parameter($applicationMock);

		$translatorMock->shouldReceive('getAvailableLocales')
			->once()
			->withNoArgs()
			->andReturn(['en']);

		Tester\Assert::null($parameterResolver->resolve($translatorMock));
	}


	public function test02(): void
	{
		$applicationMock = \Mockery::mock(Nette\Application\Application::class);
		$requestMock = \Mockery::mock(Nette\Application\Request::class);
		$translatorMock = \Mockery::mock(Translette\Translation\Translator::class);

		$applicationMock->shouldReceive('getRequests')
			->once()
			->withNoArgs()
			->andReturn([$requestMock]);

		$parameterResolver = new Translette\Translation\LocalesResolvers\Parameter($applicationMock);

		$requestMock->shouldReceive('getParameters')
			->once()
			->withNoArgs()
			->andReturn([]);

		$translatorMock->shouldReceive('getAvailableLocales')
			->once()
			->withNoArgs()
			->andReturn(['en']);

		Tester\Assert::null($parameterResolver->resolve($translatorMock));
	}


	public function test03(): void
	{
		$applicationMock = \Mockery::mock(Nette\Application\Application::class);
		$requestMock = \Mockery::mock(Nette\Application\Request::class);
		$translatorMock = \Mockery::mock(Translette\Translation\Translator::class);

		$applicationMock->shouldReceive('getRequests')
			->once()
			->withNoArgs()
			->andReturn([$requestMock]);

		$parameterResolver = new Translette\Translation\LocalesResolvers\Parameter($applicationMock);

		$requestMock->shouldReceive('getParameters')
			->once()
			->withNoArgs()
			->andReturn(['locale' => 'en']);

		$translatorMock->shouldReceive('getAvailableLocales')
			->once()
			->withNoArgs()
			->andReturn(['en']);

		Tester\Assert::same('en', $parameterResolver->resolve($translatorMock));
	}
}


$test = new Parameter($container);
$test->run();
