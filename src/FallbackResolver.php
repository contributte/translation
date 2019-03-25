<?php

/**
 * This file is part of the Translette\Translation
 */

declare(strict_types=1);

namespace Translette\Translation;

use Nette;
use Translette;


/**
 * @author Ales Wita
 * @author Filip Prochazka
 */
class FallbackResolver
{
	use Nette\SmartObject;

	/** @var array */
	private $fallbackLocales = [];


	/**
	 * @param array $fallbackLocales
	 */
	public function setFallbackLocales(array $fallbackLocales)
	{
		$this->fallbackLocales = $fallbackLocales;
	}


	public function compute(Translator $translator, $locale)
	{
		$locales = [];
		foreach ($this->fallbackLocales as $v1) {
			if ($v1 === $locale) {
				continue;
			}

			$locales[] = $v1;
		}

		if (strrchr($locale, '_') !== false) {
			array_unshift($locales, substr($locale, 0, -strlen(strrchr($locale, '_'))));
		}

		foreach ($translator->availableLocales as $v1) {
			if ($v1 === $locale) {
				continue;
			}

			if (substr($v1, 0, 2) === substr($locale, 0, 2)) {
				array_unshift($locales, $v1);
				break;
			}
		}

		return array_unique($locales);
	}
}
