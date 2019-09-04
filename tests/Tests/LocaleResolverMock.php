<?php declare(strict_types = 1);

namespace Tests;

use Contributte;

class LocaleResolverMock implements Contributte\Translation\LocalesResolvers\ResolverInterface
{

	public function resolve(Contributte\Translation\Translator $translator): ?string
	{
		return null;
	}

}
