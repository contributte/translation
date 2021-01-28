<?php declare(strict_types = 1);

namespace Tests;

use Contributte\Translation\FallbackResolver;
use Contributte\Translation\Translator;
use Mockery;
use Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';

final class FallbackResolverTest extends TestAbstract
{

	public function test01(): void
	{
		Assert::same(['cs_CZ'], $this->compute('cs', ['cs', 'cs_CZ']));
		Assert::same(['cs', 'cs_CZ'], $this->compute('sk', ['cs', 'cs_CZ']));
		Assert::same(['cs', 'en'], $this->compute('cs_CZ', ['cs', 'cs_CZ', 'en']));
		Assert::same(['en', 'cs', 'cs_CZ'], $this->compute('en_US', ['cs', 'cs_CZ', 'en', 'en_US']));
		Assert::same(['cs', 'cs_CZ', 'en', 'en_US'], $this->compute('sk', ['cs', 'cs_CZ', 'en', 'en_US']));
	}

	/**
	 * @param array<string> $fallbackLocales
	 * @return array<string>
	 */
	private function compute(
		?string $locale,
		array $fallbackLocales
	): array
	{
		$translatorMock = Mockery::mock(Translator::class);

		$translatorMock->shouldReceive('getAvailableLocales')
			->once()
			->withNoArgs()
			->andReturn($fallbackLocales);

		$resolver = new FallbackResolver();

		$resolver->setFallbackLocales($fallbackLocales);

		return $resolver->compute($translatorMock, $locale);
	}

}

(new FallbackResolverTest($container))->run();
