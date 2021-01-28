<?php declare(strict_types = 1);

namespace Tests\LocalesResolvers;

use Contributte\Translation\Exceptions\InvalidArgument;
use Contributte\Translation\LocalesResolvers\Header;
use Contributte\Translation\Translator;
use Mockery;
use Nette\Http\IRequest;
use Nette\Http\Request;
use Nette\Http\UrlImmutable;
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

	public function test02(): void
	{
		Assert::exception(function (): void {
			new Header(new class implements IRequest {

				public function getReferer(): ?UrlImmutable
				{
					return null;
				}

				public function isSameSite(): bool
				{
					return true;
				}

				public function getUrl(): UrlScript
				{
					return new UrlScript();
				}

				/**
				 * @return mixed
				 */
				public function getQuery(
					?string $key = null
				)
				{
					return null;
				}

				/**
				 * @return mixed
				 */
				public function getPost(
					?string $key = null
				)
				{
					return null;
				}

				/**
				 * @return mixed
				 */
				public function getFile(
					string $key
				)
				{
					return null;
				}

				/**
				 * @return array<\Nette\Http\FileUpload>
				 */
				public function getFiles(): array
				{
					return [];
				}

				/**
				 * @return mixed
				 */
				public function getCookie(
					string $key
				)
				{
					return null;
				}

				/**
				 * @return array<string>
				 */
				public function getCookies(): array
				{
					return [];
				}

				public function getMethod(): string
				{
					return '';
				}

				public function isMethod(
					string $method
				): bool
				{
					return true;
				}
				public function getHeader(
					string $header
				): ?string
				{
					return null;
				}

				/**
				 * @return array<string>
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
		}, InvalidArgument::class, 'Header locale resolver need "' . Request::class . '" or his child for using "detectLanguage" method.');
	}

	/**
	 * @param array<string> $availableLocales
	 */
	private function resolve(
		?string $locale,
		array $availableLocales
	): ?string
	{
		$request = new Request(new UrlScript(), null, null, null, ['Accept-Language' => $locale]);
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
