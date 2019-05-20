<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Contributte\Translation\LocalesResolvers;

use Contributte;

interface ResolverInterface
{

	public function resolve(Contributte\Translation\Translator $translator): ?string;

}
