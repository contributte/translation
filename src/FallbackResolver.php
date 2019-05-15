<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation;

use Contributte;
use Nette;


/**
 * @property      array $fallbackLocales
 *
 * @author Ales Wita
 * @author Filip Prochazka
 */
class FallbackResolver
{
	use Nette\SmartObject;

	/** @var array */
	private $fallbackLocales = [];


	/**
	 * @param array $array
	 */
	public function setFallbackLocales(array $array)
	{
		$this->fallbackLocales = $array;
	}


	/**
	 * @param Contributte\Translation\Translator $translator
	 * @param string $locale
	 * @return array
	 */
	public function compute(Translator $translator, string $locale): array
	{
		$locales = [];

		foreach ($this->fallbackLocales as $v1) {
			if ($v1 === $locale) {
				continue;
			}

			$locales[] = $v1;
		}

		if (strrchr($locale, '_') !== false) {
			array_unshift($locales, Nette\Utils\Strings::substring($locale, 0, -Nette\Utils\Strings::length(strrchr($locale, '_'))));
		}

		foreach ($translator->availableLocales as $v1) {
			if ($v1 === $locale) {
				continue;
			}

			if (Nette\Utils\Strings::substring($v1, 0, 2) === Nette\Utils\Strings::substring($locale, 0, 2)) {
				array_unshift($locales, $v1);
				break;
			}
		}

		return array_values(array_unique($locales));
	}
}
