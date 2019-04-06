<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation\Loaders;

use Nette;
use Symfony;
use Contributte;


/**
 * @author Ales Wita
 * @author Filip Prochazka
 */
class Neon extends Symfony\Component\Translation\Loader\ArrayLoader implements Symfony\Component\Translation\Loader\LoaderInterface
{
	/**
	 * {@inheritdoc}
	 *
	 * @throws Contributte\Translation\InvalidArgumentException
	 */
	public function load($resource, $locale, $domain = 'messages')
	{
		$content = @file_get_contents($resource); // @ -> prevent E_WARNING and thrown an exception

		if ($content === false) {
			throw new Contributte\Translation\InvalidArgumentException('Something wrong with resource file "' . $resource . '".');
		}

		$messages = Nette\Neon\Neon::decode($content);

		$catalogue = parent::load($messages, $locale, $domain);
		$catalogue->addResource(new Symfony\Component\Config\Resource\FileResource($resource));

		return $catalogue;
	}
}
