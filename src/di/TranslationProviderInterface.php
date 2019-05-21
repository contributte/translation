<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Contributte\Translation\DI;

interface TranslationProviderInterface
{

	/**
	 * @return string[]
	 */
	public function getTranslationResources(): array;

}
