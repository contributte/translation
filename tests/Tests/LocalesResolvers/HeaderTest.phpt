<?php declare(strict_types = 1);

namespace Tests\LocalesResolvers;

use Contributte\Translation\LocalesResolvers\Header;
use Contributte\Translation\Translator;
use Mockery;
use Nette\Http\Request;
use Nette\Http\UrlScript;
use Tester\Assert;
use Tests\TestAbstract;

$container = require __DIR__ . '/../../bootstrap.php';

final class HeaderTest extends TestAbstract
{

	public function test01(): void
	{
		Assert::null($this->resolve(null, ['foo']));
		Assert::null($this->resolve('en', []));
		Assert::null($this->resolve('foo', ['en']));
		Assert::same('en', $this->resolve('en, cs', ['en', 'cs']));
		Assert::same('en', $this->resolve('en, cs', ['cs', 'en']));
		Assert::same('en-us', $this->resolve('da, en-us;q=0.8, en;q=0.7', ['en', 'en-us']));
		Assert::same('en', $this->resolve('da, en-us;q=0.8, en;q=0.7', ['en']));
		Assert::same('en', $this->resolve('da, en_us', ['en']));
		Assert::same('en-us', $this->resolve('da, en_us', ['en', 'en-us']));
	}

	/**
	 * @param array<string> $availableLocales
	 */
	private function resolve(
		?string $locale,
		array $availableLocales
	): ?string
	{
		$request = new Request(new UrlScript(), headers: ['Accept-Language' => $locale]);
		$resolver = new Header($request);
		$translatorMock = Mockery::mock(Translator::class);

		$translatorMock->shouldReceive('getAvailableLocales')
			->once()
			->withNoArgs()
			->andReturn($availableLocales);

		return $resolver->resolve($translatorMock);
	}

}

(new HeaderTest($container))->run();
