<?php declare(strict_types = 1);

namespace Contributte\Translation;

use Nette;

class FallbackResolver
{

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

		foreach ($translator->getAvailableLocales() as $v1) {
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
