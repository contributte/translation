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
interface DatabaseInterface extends Symfony\Component\Translation\Loader\LoaderInterface
{
	/**
	 * @param mixed $parameters
	 * @return int
	 */
	function getTimestamp(...$parameters): int;
}
