<?php

/**
 * This file is part of the Translette\Translation
 */

declare(strict_types=1);

namespace Translette\Translation\LocalesResolvers;

use Translette;


/**
 * @author Ales Wita
 * @author Filip Prochazka
 */
interface ResolverInterface
{
	/**
	 * @param Translette\Translation\Translator $translator
	 * @return string|null
	 */
	public function resolve(Translette\Translation\Translator $translator): ?string;
}
