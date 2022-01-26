<?php declare(strict_types = 1);

namespace Contributte\Translation;

use Nette\DI\Container;
use Nette\Utils\Strings;

class LocaleResolver
{

	private Container $container;

	/** @var array<class-string> */
	private array $resolvers = [];

	public function __construct(
		Container $container
	)
	{
		$this->container = $container;
	}

	/**
	 * @return array<class-string>
	 */
	public function getResolvers(): array
	{
		return $this->resolvers;
	}

	/**
	 * @param class-string $resolver
	 * @return self
	 */
	public function addResolver(
		string $resolver
	): self
	{
		$this->resolvers[] = $resolver;
		return $this;
	}

	public function resolve(
		Translator $translator
	): string
	{
		foreach ($this->resolvers as $v1) {
			$resolver = $this->container
				->getByType($v1);

			$locale = $resolver->resolve($translator);

			if (
				$locale !== null &&
				(
					$translator->getLocalesWhitelist() === null ||
					in_array(
						Strings::substring($locale, 0, 2),
						array_map(
							fn (string $locale): string => Strings::substring($locale, 0, 2),
							$translator->getLocalesWhitelist()
						),
						true
					)
				)
			) {
				return $locale;
			}
		}

		return $translator->getDefaultLocale();
	}

}
