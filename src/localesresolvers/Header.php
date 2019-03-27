<?php

/**
 * This file is part of the Translette/Translation
 */

declare(strict_types=1);

namespace Translette\Translation\LocalesResolvers;

use Nette;
use Translette;


/**
 * @author Ales Wita
 * @author Filip Prochazka
 */
class Header implements ResolverInterface
{
	use Nette\SmartObject;

	/** @var Nette\Http\Request */
	private $httpRequest;


	/**
	 * @param Nette\Http\Request $httpRequest
	 */
	public function __construct(Nette\Http\Request $httpRequest)
	{
		$this->httpRequest = $httpRequest;
	}


	/**
	 * @param Translette\Translation\Translator $translator
	 * @return string|null
	 */
	public function resolve(Translette\Translation\Translator $translator): ?string
	{
		$langs = [];

		foreach ($translator->availableLocales as $v1) {
			$langs[] = $v1;

			if (Nette\Utils\Strings::length($v1) > 2) {
				$langs[] = Nette\Utils\Strings::substring($v1, 0, 2);// en_US => en
			}
		}

		if (count($langs) === 0) {
			return null;
		}

		return $this->httpRequest->detectLanguage($langs);
	}
}
