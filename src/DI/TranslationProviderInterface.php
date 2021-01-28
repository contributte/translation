<?php declare(strict_types = 1);

namespace Contributte\Translation\DI;

interface TranslationProviderInterface
{

	/**
	 * @return array<string>
	 */
	public function getTranslationResources(): array;

}
