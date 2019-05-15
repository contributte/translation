<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation;

use Contributte;


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
	 * @return Contributte\Translation\PrefixedTranslator
	 */
	public function domain(string $prefix): PrefixedTranslator
	{
		return $this->createPrefixedTranslator($prefix);
	}
}
