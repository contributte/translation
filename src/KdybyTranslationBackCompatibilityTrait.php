<?php

/**
 * This file is part of the Translette/Translation
 */

declare(strict_types=1);

namespace Translette\Translation;

use Translette;


/**
 * @method createPrefixedTranslator(string $prefix)
 *
 * @author Ales Wita
 */
trait KdybyTranslationBackCompatibilityTrait
{
	/**
	 * @deprecated
	 *
	 * @param string $prefix
	 * @return Translette\Translation\PrefixedTranslator
	 */
	public function domain(string $prefix): PrefixedTranslator
	{
		return $this->createPrefixedTranslator($prefix);
	}
}
