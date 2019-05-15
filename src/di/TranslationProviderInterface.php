<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation\DI;


/**
 * @author Ales Wita
 * @author Filip Prochazka
 */
interface TranslationProviderInterface
{
	/**
	 * @return array
	 */
	public function getTranslationResources(): array;
}
