<?php

/**
 * This file is part of the Translette/Translation
 */

declare(strict_types=1);

namespace Translette\Translation\Tests\Tests;

use Nette;
use Tester;
use Translette;

$container = require __DIR__ . '/../bootstrap.php';


/**
 * @author Ales Wita
 */
class FallbackResolver extends Tester\TestCase
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
		Tester\Assert::same(['cs_CZ'], $this->compute('cs', ['cs', 'cs_CZ']));
		Tester\Assert::same(['cs', 'cs_CZ'], $this->compute('sk', ['cs', 'cs_CZ']));
		Tester\Assert::same(['cs', 'en'], $this->compute('cs_CZ', ['cs', 'cs_CZ', 'en']));
		Tester\Assert::same(['en', 'cs', 'cs_CZ'], $this->compute('en_US', ['cs', 'cs_CZ', 'en', 'en_US']));
		Tester\Assert::same(['cs', 'cs_CZ', 'en', 'en_US'], $this->compute('sk', ['cs', 'cs_CZ', 'en', 'en_US']));
	}


	/**
	 * @internal
	 *
	 * @param string $locale
	 * @param array $fallbackLocales
	 * @return array
	 */
	private function compute(?string $locale, array $fallbackLocales): array
	{
		$translatorMock = \Mockery::mock(Translette\Translation\Translator::class);

		$translatorMock->shouldReceive('getAvailableLocales')
			->once()
			->withNoArgs()
			->andReturn($fallbackLocales);

		$resolver = new Translette\Translation\FallbackResolver;

		$resolver->setFallbackLocales($fallbackLocales);

		return $resolver->compute($translatorMock, $locale);
	}
}


$test = new FallbackResolver($container);
$test->run();
