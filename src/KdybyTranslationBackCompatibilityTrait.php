<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Contributte\Translation;

/**
 * @method createPrefixedTranslator(string $prefix)
 */
trait KdybyTranslationBackCompatibilityTrait
{

	/**
	 * @deprecated
	 */
	public function domain(string $prefix): PrefixedTranslator
	{
		return $this->createPrefixedTranslator($prefix);
	}

}
