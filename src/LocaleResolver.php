<?php

/**
 * This file is part of the Translette\Translation
 */

declare(strict_types=1);

namespace Translette\Translation;

use Nette;
use Translette;


/**
 * @property-read $resolvers
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
	 * @param Translette\Translation\LocalesResolvers\ResolverInterface $resolver
	 * @return self
	 */
	public function addResolver(LocalesResolvers\ResolverInterface $resolver): self
	{
		$this->resolvers[] = $resolver;
		return $this;
	}


	/**
	 * @param Translette\Translation\Translator $translator
	 * @return string
	 */
	public function resolve(Translator $translator): string
	{
		/** @var Translette\Translation\LocalesResolvers\ResolverInterface $v1 */
		foreach ($this->resolvers as $v1) {
			$locale = $v1->resolve($translator);

			if ($locale !== null) {
				return $locale;
			}
		}

		return $translator->defaultLocale;
	}
}
