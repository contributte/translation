<?php declare(strict_types = 1);

namespace Contributte\Translation;

use Nette\DI\Container;
use Nette\Utils\Strings;

class LocaleResolver
{

	private Container $container;

	/** @var array<string> */
	private array $resolvers = [];

	public function __construct(
		Container $container
	)
	{
		$this->container = $container;
	}

	/**
	 * @return array<string>
	 */
	public function getResolvers(): array
	{
		return $this->resolvers;
	}

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
			/** @var \Contributte\Translation\LocalesResolvers\ResolverInterface $resolver */
			$resolver = $this->container->getByType($v1);
			$locale = $resolver->resolve($translator);

			if ($locale !== null && ($translator->getLocalesWhitelist() === null || in_array(Strings::substring($locale, 0, 2), array_map(function ($locale): string {
				return Strings::substring($locale, 0, 2);
			}, $translator->getLocalesWhitelist()), true))) {
				return $locale;
			}
		}

		return $translator->getDefaultLocale();
	}

}
