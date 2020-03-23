<?php declare(strict_types = 1);

namespace Contributte\Translation;

use Contributte;
use Nette;

/**
 * @property-read array $resolvers
 */
class LocaleResolver
{

	use Nette\SmartObject;

	/** @var Nette\DI\Container */
	private $container;

	/** @var string[] */
	private $resolvers = [];

	public function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
	}

	/**
	 * @return string[]
	 */
	public function getResolvers(): array
	{
		return $this->resolvers;
	}

	public function addResolver(string $resolver): self
	{
		$this->resolvers[] = $resolver;
		return $this;
	}

	public function resolve(Translator $translator): string
	{
		foreach ($this->resolvers as $v1) {
			/** @var Contributte\Translation\LocalesResolvers\ResolverInterface $resolver */
			$resolver = $this->container->getByType($v1);
			$locale = $resolver->resolve($translator);

			if ($locale !== null && ($translator->getLocalesWhitelist() === null || in_array(Nette\Utils\Strings::substring($locale, 0, 2), array_map(function ($locale): string {
				return Nette\Utils\Strings::substring($locale, 0, 2);
			}, $translator->getLocalesWhitelist()), true))) {
				return $locale;
			}
		}

		return $translator->defaultLocale;
	}

}
