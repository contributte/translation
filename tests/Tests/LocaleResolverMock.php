<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Tests;

use Contributte;

class LocaleResolverMock implements Contributte\Translation\LocalesResolvers\ResolverInterface
{

	public function resolve(Contributte\Translation\Translator $translator): ?string
	{
		return null;
	}

}
