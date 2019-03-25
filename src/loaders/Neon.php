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
		if (!stream_is_local($resource)) {
			throw new Translette\Translation\InvalidArgumentException('This is not a local file "' . $resource . '".');
		}

		if (!file_exists($resource)) {
			throw new Translette\Translation\FileNotFoundException('File "' . $resource . '" not found.');
		}

		$messages = Nette\Neon\Neon::decode(file_get_contents($resource));

		$catalogue = parent::load($messages, $locale, $domain);
		$catalogue->addResource(new Symfony\Component\Config\Resource\FileResource($resource));

		return $catalogue;
	}
}
