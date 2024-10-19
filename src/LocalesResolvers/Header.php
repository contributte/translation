<?php declare(strict_types = 1);

namespace Contributte\Translation\LocalesResolvers;

use Contributte\Translation\Translator;
use Nette\Http\IRequest;
use Nette\Http\Request;
use Nette\Utils\Strings;

class Header implements ResolverInterface
{

	private IRequest $httpRequest;

	public function __construct(
		IRequest $httpRequest
	)
	{
		$this->httpRequest = $httpRequest;
	}

	public function resolve(
		Translator $translator
	): ?string
	{
		/** @var array<string> $locales */
		$locales = [];

		foreach ($translator->getAvailableLocales() as $v1) {
			$locales[] = $v1;

			if (Strings::length($v1) < 3) {
				continue;
			}

			$locales[] = Strings::substring($v1, 0, 2);// en_US => en
		}

		if (count($locales) === 0) {
			return null;
		}

		return (new Request(
			$this->httpRequest->getUrl(),
			headers: $this->httpRequest->getHeaders()
		))->detectLanguage($locales);
	}

}
