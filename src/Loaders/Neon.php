<?php declare(strict_types = 1);

namespace Contributte\Translation\Loaders;

use Contributte\Translation\Exceptions\InvalidArgument;
use Nette;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Loader\LoaderInterface;

class Neon extends ArrayLoader implements LoaderInterface
{

	/**
	 * {@inheritdoc}
	 *
	 * @throws \Contributte\Translation\Exceptions\InvalidArgument
	 */
	public function load(
		$resource,
		$locale,
		$domain = 'messages'
	)
	{
		$content = @file_get_contents($resource); // @ -> prevent E_WARNING and thrown an exception

		if ($content === false) {
			throw new InvalidArgument('Something wrong with resource file "' . $resource . '".');
		}

		$messages = Nette\Neon\Neon::decode($content);

		$catalogue = parent::load($messages ?? [], $locale, $domain);
		$catalogue->addResource(new FileResource($resource));

		return $catalogue;
	}

}
