<?php declare(strict_types=1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Contributte\Translation;

use Contributte;
use Nette;

/**
 * @property-read array $resolvers
 */
class LocaleResolver
{

	use Nette\SmartObject;

	/** @var array */
	private $resolvers = [];

	public function getResolvers(): array
	{
		return $this->resolvers;
	}

	public function addResolver(LocalesResolvers\ResolverInterface $resolver): self
	{
		$this->resolvers[] = $resolver;
		return $this;
	}

	public function resolve(Translator $translator): string
	{
		/** @var Contributte\Translation\LocalesResolvers\ResolverInterface $v1 */
		foreach ($this->resolvers as $v1) {
			$locale = $v1->resolve($translator);

			if ($locale !== null) {
				return $locale;
			}
		}

		return $translator->defaultLocale;
	}

}
