<?php declare(strict_types = 1);

namespace Contributte\Translation\Loaders;

use Contributte\Translation\Exceptions\InvalidArgument;
use Nette\Neon\Neon as NetteNeon;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

class Neon extends ArrayLoader implements LoaderInterface
{

	/**
	 * {@inheritdoc}
	 *
	 * @throws \Contributte\Translation\Exceptions\InvalidArgument
	 */
	public function load(
		mixed $resource,
		string $locale,
		string $domain = 'messages'
	): MessageCatalogue
	{
		if (!\is_string($resource)) {
			throw new InvalidArgument('Parameter resource must be string.');
		}

		if (!\is_readable($resource)) {
			throw new InvalidArgument('Something wrong with resource file "' . $resource . '".');
		}

		$messages = NetteNeon::decodeFile($resource);

		$catalogue = parent::load($messages ?? [], $locale, $domain);

		$catalogue->addResource(new FileResource($resource));

		return $catalogue;
	}

}
