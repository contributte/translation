<?php

/**
 * This file is part of the Translette\Translation
 */

declare(strict_types=1);

namespace Translette\Translation;

use Nette;


/**
 * @author Ales Wita
 * @author Filip Prochazka
 */
class Helpers
{
	use Nette\StaticClass;

	/**
	 * @param array|null $whitelist
	 * @return string|null
	 */
	public static function whitelistRegexp(?array $whitelist): ?string
	{
		return $whitelist !== null ? '~^(' . implode('|', $whitelist) . ')~i' : null;
	}


	/**
	 * @param string|null $message
	 * @return array
	 */
	public static function extractMessage(?string $message): array
	{
		if ($message !== null && strpos($message, '.') !== false && strpos($message, ' ') === false) {
			[$domain, $message] = explode('.', $message, 2);

		} else {
			$domain = null;
		}

		return [$domain, $message];
	}
}
