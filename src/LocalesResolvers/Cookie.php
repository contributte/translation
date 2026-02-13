<?php declare(strict_types=1);

namespace Contributte\Translation\LocalesResolvers;

use Contributte\Translation\Translator;
use Nette\Http\IRequest;
use Nette\Http\IResponse;

class Cookie implements ResolverInterface
{

	public static string $parameter = 'locale';

	public static ?string $expire = '+1 year';

	private IRequest $httpRequest;

	private IResponse $httpResponse;

	public function __construct(
		IRequest $httpRequest,
		IResponse $httpResponse,
	)
	{
		$this->httpRequest = $httpRequest;
		$this->httpResponse = $httpResponse;
	}

	public function setLocale(
		?string $locale,
		?string $expire = null,
	): self
	{
		if (is_string($locale)) {
			$this->httpResponse->setCookie(self::$parameter, $locale, $expire ?? self::$expire);

		} else {
			$this->httpResponse->deleteCookie(self::$parameter);

		}

		return $this;
	}

	public function resolve(
		Translator $translator,
	): ?string
	{
		$locale = $this->httpRequest->getCookie(self::$parameter);

		if (is_string($locale)) {
			return $locale;
		}

		return null;
	}

}
