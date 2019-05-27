<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Tests\LocalesResolvers;

use Contributte;
use Mockery;
use Nette;
use Tester;
use Tests;

$container = require __DIR__ . '/../../bootstrap.php';

class Header extends Tests\TestAbstract
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

	public function test02(): void
	{
		Tester\Assert::exception(function (): void {
			new Contributte\Translation\LocalesResolvers\Header(new class implements Nette\Http\IRequest {

				public function getReferer(): ?Nette\Http\UrlImmutable
				{
					return null;
				}

				public function isSameSite(): bool
				{
					return true;
				}

				public function getUrl(): Nette\Http\UrlScript
				{
					return new Nette\Http\UrlScript();
				}

				/**
				 * @return mixed
				 */
				public function getQuery(?string $key = null)
				{
					return null;
				}

				/**
				 * @return mixed
				 */
				public function getPost(?string $key = null)
				{
					return null;
				}

				/**
				 * @return mixed
				 */
				public function getFile(string $key)
				{
					return null;
				}

				/**
				 * @return Nette\Http\FileUpload[]
				 */
				public function getFiles(): array
				{
					return [];
				}

				/**
				 * @return mixed
				 */
				public function getCookie(string $key)
				{
					return null;
				}

				/**
				 * @return string[]
				 */
				public function getCookies(): array
				{
					return [];
				}

				public function getMethod(): string
				{
					return '';
				}

				public function isMethod(string $method): bool
				{
					return true;
				}
				public function getHeader(string $header): ?string
				{
					return null;
				}
				
				/**
				 * @return string[]
				 */
				public function getHeaders(): array
				{
					return [];
				}

				public function isSecured(): bool
				{
					return true;
				}

				public function isAjax(): bool
				{
					return true;
				}

				public function getRemoteAddress(): ?string
				{
					return null;
				}

				public function getRemoteHost(): ?string
				{
					return null;
				}

				public function getRawBody(): ?string
				{
					return null;
				}

			});
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'Header locale resolver need "Nette\\Http\\Request" or his child for using "detectLanguage" method.');
	}

	/**
	 * @param string[] $availableLocales
	 * @internal
	 */
	private function resolve(?string $locale, array $availableLocales): ?string
	{
		$request = new Nette\Http\Request(new Nette\Http\UrlScript(), null, null, null, ['Accept-Language' => $locale]);
		$resolver = new Contributte\Translation\LocalesResolvers\Header($request);
		$translatorMock = Mockery::mock(Contributte\Translation\Translator::class);

		$translatorMock->shouldReceive('getAvailableLocales')
			->once()
			->withNoArgs()
			->andReturn($availableLocales);

		return $resolver->resolve($translatorMock);
	}

}


(new Header($container))->run();
