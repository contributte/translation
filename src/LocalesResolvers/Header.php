<?php declare(strict_types = 1);

namespace Contributte\Translation\LocalesResolvers;

use Contributte\Translation\Exceptions\InvalidArgument;
use Contributte\Translation\Translator;
use Nette\Http\IRequest;
use Nette\Http\Request;
use Nette\Utils\Strings;

class Header implements ResolverInterface
{

	/** @var \Nette\Http\Request */
	private $httpRequest;

	/**
	 * @throws \Contributte\Translation\Exceptions\InvalidArgument
	 */
	public function __construct(
		IRequest $httpRequest
	)
	{
		if (!is_a($httpRequest, Request::class, true)) {
			throw new InvalidArgument('Header locale resolver need "Nette\\Http\\Request" or his child for using "detectLanguage" method.');
		}

		$this->httpRequest = $httpRequest;
	}

	public function resolve(
		Translator $translator
	): ?string
	{
		/** @var array<string> $langs */
		$langs = [];

		foreach ($translator->getAvailableLocales() as $v1) {
			$langs[] = $v1;

			if (Strings::length($v1) < 3) {
				continue;
			}

			$langs[] = Strings::substring($v1, 0, 2);// en_US => en
		}

		if (count($langs) === 0) {
			return null;
		}

		return $this->httpRequest->detectLanguage($langs);
	}

}
