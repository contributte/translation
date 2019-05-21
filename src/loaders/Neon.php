<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Contributte\Translation\Loaders;

use Contributte;
use Nette;
use Symfony;

class Neon extends Symfony\Component\Translation\Loader\ArrayLoader implements Symfony\Component\Translation\Loader\LoaderInterface
{

	/**
	 * {@inheritdoc}
	 *
	 * @throws Contributte\Translation\Exceptions\InvalidArgument
	 */
	public function load($resource, $locale, $domain = 'messages')
	{
		$content = @file_get_contents($resource); // @ -> prevent E_WARNING and thrown an exception

		if ($content === false) {
			throw new Contributte\Translation\Exceptions\InvalidArgument('Something wrong with resource file "' . $resource . '".');
		}

		$messages = Nette\Neon\Neon::decode($content);

		$catalogue = parent::load($messages ?? [], $locale, $domain);
		$catalogue->addResource(new Symfony\Component\Config\Resource\FileResource($resource));

		return $catalogue;
	}

}
