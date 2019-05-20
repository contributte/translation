<?php declare(strict_types=1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Contributte\Translation;

use Latte;
use Nette;

class Helpers
{

	use Nette\StaticClass;

	public static function whitelistRegexp(?array $whitelist): ?string
	{
		return $whitelist !== null ? '~^(' . implode('|', $whitelist) . ')~i' : null;
	}

	public static function extractMessage(string $message): array
	{
		if (strpos($message, '.') !== false && strpos($message, ' ') === false) {
			[$domain, $message] = explode('.', $message, 2);

		} else {
			$domain = null;
		}

		return [$domain, $message];
	}

	public static function macroWithoutParameters(Latte\MacroNode $node): bool
	{
		$result = Nette\Utils\Strings::trim($node->tokenizer->joinUntil(',')) === Nette\Utils\Strings::trim($node->args);
		$node->tokenizer->reset();
		return $result;
	}

}
