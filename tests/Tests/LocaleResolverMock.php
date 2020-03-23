<?php declare(strict_types = 1);

namespace Tests;

use Contributte;

class LocaleResolverMock implements Contributte\Translation\LocalesResolvers\ResolverInterface
{

	/** @var string|null */
	private $locale;

	public function setLocale(?string $locale): self
	{
		$this->locale = $locale;
		return $this;
	}

	public function resolve(Contributte\Translation\Translator $translator): ?string
	{
		return $this->locale;
	}

}
