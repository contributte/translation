<?php declare(strict_types = 1);

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
	public function load($resource, string $locale, string $domain = 'messages')
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
