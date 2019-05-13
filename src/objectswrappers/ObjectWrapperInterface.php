<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation\ObjectsWrappers;


/**
 * @author Ales Wita
 */
interface ObjectWrapperInterface
{
	/**
	 * @return string|null
	 */
	function getMessage(): ?string;

	/**
	 * @param string $string
	 */
	function setMessage(string $string): void;
}
