<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation\LocalesResolvers;

use Contributte;


/**
 * @author Ales Wita
 * @author Filip Prochazka
 */
interface ResolverInterface
{
	/**
	 * @param Contributte\Translation\Translator $translator
	 * @return string|null
	 */
	public function resolve(Contributte\Translation\Translator $translator): ?string;
}
