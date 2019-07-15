<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Tests;

use Contributte;
use Mockery;
use Tester;
use Tests;

$container = require __DIR__ . '/../bootstrap.php';

class FallbackResolver extends Tests\TestAbstract
{

	public function test01(): void
	{
		Tester\Assert::same(['cs_CZ'], $this->compute('cs', ['cs', 'cs_CZ']));
		Tester\Assert::same(['cs', 'cs_CZ'], $this->compute('sk', ['cs', 'cs_CZ']));
		Tester\Assert::same(['cs', 'en'], $this->compute('cs_CZ', ['cs', 'cs_CZ', 'en']));
		Tester\Assert::same(['en', 'cs', 'cs_CZ'], $this->compute('en_US', ['cs', 'cs_CZ', 'en', 'en_US']));
		Tester\Assert::same(['cs', 'cs_CZ', 'en', 'en_US'], $this->compute('sk', ['cs', 'cs_CZ', 'en', 'en_US']));
	}

	/**
	 * @param string[] $fallbackLocales
	 * @return string[]
	 * @internal
	 */
	private function compute(?string $locale, array $fallbackLocales): array
	{
		$translatorMock = Mockery::mock(Contributte\Translation\Translator::class);

		$translatorMock->shouldReceive('getAvailableLocales')
			->once()
			->withNoArgs()
			->andReturn($fallbackLocales);

		$resolver = new Contributte\Translation\FallbackResolver();

		$resolver->setFallbackLocales($fallbackLocales);

		return $resolver->compute($translatorMock, $locale);
	}

}

(new FallbackResolver($container))->run();
