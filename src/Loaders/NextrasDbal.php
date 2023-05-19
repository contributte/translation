<?php declare(strict_types = 1);

namespace Contributte\Translation\Loaders;

use Contributte\Translation\Exceptions\InvalidState;
use Nextras\Dbal\Connection;
use Nextras\Dbal\Drivers\Exception\QueryException;
use Symfony\Component\Translation\Loader\LoaderInterface;

class NextrasDbal extends DatabaseAbstract implements LoaderInterface
{

	private Connection $connection;

	public function __construct(
		Connection $connection
	)
	{
		$this->connection = $connection;
	}

	/**
	 * @inheritdoc
	 * @throws \Contributte\Translation\Exceptions\InvalidState
	 * @throws QueryException
	 */
	protected function getMessages(
		array $config,
		string $resource,
		string $locale,
		string $domain
	): array
	{
		$messages = [];

		$result = $this->connection
			->query(
				'SELECT %column AS `id`, %column AS `locale`, %column AS `message` FROM %table WHERE %column = %s',
				$config['id'],
				$config['locale'],
				$config['message'],
				$config['table'],
				$config['locale'],
				$locale
			)
			->fetchAll();

		foreach ($result as $row) {
			$row = (array) $row;
			if (array_key_exists($row['id'], $messages)) {
				throw new InvalidState('Id "' . $row['id'] . '" declared twice in "' . $config['table'] . '" table/domain.');
			}

			$messages[$row['id']] = $row['message'];
		}

		return $messages;
	}

}
