<?php declare(strict_types = 1);

namespace Contributte\Translation;

use Latte\MacroNode;
use Nette\Utils\Strings;

class Helpers
{

	/**
	 * @param array<string>|null $whitelist
	 */
	public static function whitelistRegexp(
		?array $whitelist
	): ?string
	{
		return $whitelist !== null ? '~^(' . implode('|', $whitelist) . ')~i' : null;
	}

	/**
	 * @return array<string>
	 */
	public static function extractMessage(
		string $message
	): array
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

	public static function macroWithoutParameters(
		MacroNode $node
	): bool
	{
		$result = Strings::trim($node->tokenizer->joinUntil(',')) === Strings::trim($node->args);
		$node->tokenizer->reset();
		return $result;
	}

}
