<?php

/**
 * This file is part of the Translette\Translation
 */

declare(strict_types=1);

namespace Translette\Translation\Latte;

use Nette;


/**
 * @author Ales Wita
 * @author Filip Prochazka
 */
class Filters
{
	use Nette\SmartObject;

	/** @var Nette\Localization\ITranslator */
	private $translator;


	/**
	 * @param Nette\Localization\ITranslator $translator
	 */
	public function __construct(Nette\Localization\ITranslator $translator)
	{
		$this->translator = $translator;
	}


	/**
	 * @param string $message
	 * @param int|array|null $count
	 * @param string|array|null $parameters
	 * @param string|null $domain
	 * @param string|null $locale
	 * @return string
	 */
	public function translate($message, $count = null, $parameters = [], $domain = null, $locale = null)
	{
		if (is_array($count)) {
			$locale = ($domain !== null) ? (string) $domain : null;
			$domain = ($parameters !== null && !empty($parameters)) ? (string) $parameters : null;
			$parameters = $count;
			$count = null;
		}

		return $this->translator->translate($message, ($count !== null) ? (int) $count : null, (array) $parameters, $domain, $locale);
	}
}
