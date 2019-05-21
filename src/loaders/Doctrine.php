<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Contributte\Translation\Loaders;

use Contributte;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use stdClass;
use Symfony;

class Doctrine extends DatabaseAbstract implements Symfony\Component\Translation\Loader\LoaderInterface
{

	/** @var EntityManagerDecorator $em */
	private $em;

	public function __construct(EntityManagerDecorator $em)
	{
		$this->em = $em;
	}

	/**
	 * @return string[]
	 * @throws Contributte\Translation\Exceptions\InvalidState
	 */
	protected function getMessages(stdClass $config, string $resource, string $locale, string $domain): array
	{
		$messages = [];

		foreach ($this->em->getRepository($config->table)->findBy([$config->locale => $locale]) as $v1) {
			$id = $v1->{$config->id};
			$message = $v1->{$config->message};

			if (array_key_exists($id, $messages)) {
				throw new Contributte\Translation\Exceptions\InvalidState('Id "' . $id . '" declared twice in "' . $config->table . '" table/domain.');
			}

			$messages[$id] = $message;
		}

		return $messages;
	}

}
