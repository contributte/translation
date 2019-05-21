<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Contributte\Translation;

use Nette;

/**
 * @property      array $fallbackLocales
 */
class FallbackResolver
{

	use Nette\SmartObject;

	/** @var string[] */
	private $fallbackLocales = [];

	/**
	 * @param string[] $array
	 */
	public function setFallbackLocales(array $array): self
	{
		$this->fallbackLocales = $array;
		return $this;
	}

	/**
	 * @return string[]
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
