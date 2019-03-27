<?php

/**
 * This file is part of the Translette\Translation
 */

declare(strict_types=1);

namespace Translette\Translation\Loaders;

use Nette;
use Symfony;
use Translette;


/**
 * @author Ales Wita
 * @author Filip Prochazka
 */
class Neon extends Symfony\Component\Translation\Loader\ArrayLoader implements Symfony\Component\Translation\Loader\LoaderInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function load($resource, $locale, $domain = 'messages')
	{
		$content = file_get_contents($resource);

		if ($content === false) {
			throw new Translette\Translation\InvalidArgumentException('Something wrong with resource file "' . $resource . '".');
		}

		$messages = Nette\Neon\Neon::decode($content);

		$catalogue = parent::load($messages, $locale, $domain);
		$catalogue->addResource(new Symfony\Component\Config\Resource\FileResource($resource));

		return $catalogue;
	}
}
