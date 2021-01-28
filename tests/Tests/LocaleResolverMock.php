<?php declare(strict_types = 1);

namespace Tests;

use Contributte\Translation\LocalesResolvers\ResolverInterface;
use Contributte\Translation\Translator;

final class LocaleResolverMock implements ResolverInterface
{

	private ?string $locale;

	public function setLocale(
		?string $locale
	): self
	{
		$this->locale = $locale;
		return $this;
	}

	public function resolve(
		Translator $translator
	): ?string
	{
		return $this->locale;
	}

}
