<?php declare(strict_types=1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Contributte\Translation\Loaders;

use Contributte;
use Symfony;

class Doctrine extends DatabaseAbstract implements Symfony\Component\Translation\Loader\LoaderInterface
{

	/** @var \Doctrine\ORM\Decorator\EntityManagerDecorator $em */
	private $em;

	public function __construct(\Doctrine\ORM\Decorator\EntityManagerDecorator $em)
	{
		$this->em = $em;
	}

	/**
	 * @throws Contributte\Translation\InvalidStateException
	 */
	protected function getMessages(\stdClass $config, string $resource, string $locale, string $domain): array
	{
		$messages = [];

		foreach ($this->em->getRepository($config->table)->findBy([$config->locale => $locale]) as $v1) {
			$id = $v1->{$config->id};
			$message = $v1->{$config->message};

			if (array_key_exists($id, $messages)) {
				throw new Contributte\Translation\InvalidStateException('Id "' . $id . '" declared twice in "' . $config->table . '" table/domain.');
			}

			$messages[$id] = $message;
		}

		return $messages;
	}

}
