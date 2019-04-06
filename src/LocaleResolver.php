<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation;

use Nette;
use Contributte;


/**
 * @property-read array $resolvers
 *
 * @author Ales Wita
 * @author Filip Prochazka
 */
class LocaleResolver
{
	use Nette\SmartObject;

	/** @var array */
	private $resolvers = [];


	/**
	 * @return array
	 */
	public function getResolvers(): array
	{
		return $this->resolvers;
	}


	/**
	 * @param Contributte\Translation\LocalesResolvers\ResolverInterface $resolver
	 * @return self
	 */
	public function addResolver(LocalesResolvers\ResolverInterface $resolver): self
	{
		$this->resolvers[] = $resolver;
		return $this;
	}


	/**
	 * @param Contributte\Translation\Translator $translator
	 * @return string
	 */
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
