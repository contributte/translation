<?php declare(strict_types = 1);

namespace Contributte\Translation\LocalesResolvers;

use Contributte\Translation\Translator;

interface ResolverInterface
{

	public function resolve(
		Translator $translator
	): ?string;

}
