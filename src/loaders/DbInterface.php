<?php

/**
 * This file is part of the Translette/Translation
 */

declare(strict_types=1);

namespace Translette\Translation\Loaders;

use Symfony;


/**
 * @author Ales Wita
 */
interface DbInterface extends Symfony\Component\Translation\Loader\LoaderInterface
{
	/**
	 * @param string $locale
	 * @return int
	 */
	function getUpdateTimestamp(string $locale): int;
}
