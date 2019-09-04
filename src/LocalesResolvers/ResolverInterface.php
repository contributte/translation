<?php declare(strict_types = 1);

namespace Contributte\Translation\LocalesResolvers;

use Contributte;

interface ResolverInterface
{

	public function resolve(Contributte\Translation\Translator $translator): ?string;

}
