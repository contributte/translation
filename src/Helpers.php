<?php declare(strict_types = 1);

namespace Contributte\Translation;

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

	public static function isAbsoluteMessage(
		string $message
	): bool
	{
		return Strings::startsWith($message, '//');
	}

	/**
	 * @param mixed $message
	 * @param array<string>|null $prefix
	 * @return mixed
	 */
	public static function prefixMessage(
		$message,
		?array $prefix
	)
	{
		if (is_string($message) && $prefix !== null && !self::isAbsoluteMessage($message)) {
			$message = implode('.', $prefix) . '.' . $message;
		}

		return $message;
	}

	public static function createLatteProperty(
		string $suffix
	): string
	{
		return '$ᴛ_contributteTranslation' . $suffix;
	}

}
