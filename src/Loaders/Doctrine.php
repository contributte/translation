<?php declare(strict_types = 1);

namespace Contributte\Translation\Loaders;

use Contributte\Translation\Exceptions\InvalidState;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Symfony\Component\Translation\Loader\LoaderInterface;

class Doctrine extends DatabaseAbstract implements LoaderInterface
{

	private EntityManagerDecorator $em;

	public function __construct(
		EntityManagerDecorator $em
	)
	{
		$this->em = $em;
	}

	/**
	 * @inheritdoc
	 * @throws \Contributte\Translation\Exceptions\InvalidState
	 */
	protected function getMessages(
		array $config,
		string $resource,
		string $locale,
		string $domain
	): array
	{
		$messages = [];

		/** @var class-string $className */
		$className = $config['table'];

		/** @var array<object> $result */
		$result = $this->em
			->getRepository($className)
			->findBy(
				[
					$config['locale'] => $locale,
				]
			);

		foreach ($result as $v1) {
			$id = $v1->{$config['id']};
			$message = $v1->{$config['message']};

			if (array_key_exists($id, $messages)) {
				throw new InvalidState('Id "' . $id . '" declared twice in "' . $config['table'] . '" table/domain.');
			}

			$messages[$id] = $message;
		}

		return $messages;
	}

}
