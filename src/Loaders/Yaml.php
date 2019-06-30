<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Contributte\Translation\Loaders;

use Contributte;
use Symfony;

class Yaml extends Symfony\Component\Translation\Loader\YamlFileLoader implements Symfony\Component\Translation\Loader\LoaderInterface
{

	/**
	 * {@inheritdoc}
	 *
	 * @throws Contributte\Translation\Exceptions\InvalidArgument
	 */
	public function load($resource, $locale, $domain = 'messages')
	{
		try {
			$content = parent::load($resource, $locale, $domain);
		} catch (\Throwable $e) {
			throw new Contributte\Translation\Exceptions\InvalidArgument('Something wrong with resource file "' . $resource . '".');
		}

		return $content;
	}

}
