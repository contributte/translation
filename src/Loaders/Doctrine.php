<?php declare(strict_types = 1);

namespace Contributte\Translation\Loaders;

use Contributte\Translation\Exceptions\InvalidState;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use stdClass;
use Symfony\Component\Translation\Loader\LoaderInterface;

class Doctrine extends DatabaseAbstract implements LoaderInterface
{

	/** @var \Doctrine\ORM\Decorator\EntityManagerDecorator $em */
	private $em;

	public function __construct(
		EntityManagerDecorator $em
	)
	{
		$this->em = $em;
	}

	/**
	 * @return array<string>
	 * @throws \Contributte\Translation\Exceptions\InvalidState
	 */
	protected function getMessages(
		stdClass $config,
		string $resource,
		string $locale,
		string $domain
	): array
	{
		$messages = [];

		foreach ($this->em->getRepository($config->table)->findBy([$config->locale => $locale]) as $v1) {
			$id = $v1->{$config->id};
			$message = $v1->{$config->message};

			if (array_key_exists($id, $messages)) {
				throw new InvalidState('Id "' . $id . '" declared twice in "' . $config->table . '" table/domain.');
			}

			$messages[$id] = $message;
		}

		return $messages;
	}

}
