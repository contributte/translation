<?php declare(strict_types = 1);

namespace Contributte\Translation;

use Latte;
use Nette;

class Helpers
{

	use Nette\StaticClass;

	/**
	 * @param string[]|null $whitelist
	 */
	public static function whitelistRegexp(?array $whitelist): ?string
	{
		return $whitelist !== null ? '~^(' . implode('|', $whitelist) . ')~i' : null;
	}

	/**
	 * @return string[]
	 */
	public static function extractMessage(string $message): array
	{
		$dot = strpos($message, '.');
		$space = strpos($message, ' ');

		if ($dot !== false && ($space === false || $dot < $space)) {
			[$domain, $message] = explode('.', $message, 2);

		} else {
			$domain = 'messages';
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
