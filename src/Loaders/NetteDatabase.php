<?php declare(strict_types = 1);

namespace Contributte\Translation\Loaders;

use Contributte\Translation\Exceptions\InvalidState;
use Nette\Database\Connection;
use Symfony\Component\Translation\Loader\LoaderInterface;

class NetteDatabase extends DatabaseAbstract implements LoaderInterface
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
	 *
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

		/** @var array<array{id: int|string, locale: string, message: string}> $result */
		$result = $this->connection
			->query(
				'SELECT ? AS `id`, ? AS `locale`, ? AS `message` FROM ? WHERE ?',
				Connection::literal($config['id']),
				Connection::literal($config['locale']),
				Connection::literal($config['message']),
				Connection::literal($config['table']),
				Connection::literal('?', [$config['locale'] => $locale])
			)
			->fetchAll();

		foreach ($result as $row) {
			if (array_key_exists($row['id'], $messages)) {
				throw new InvalidState('Id "' . $row['id'] . '" declared twice in "' . $config['table'] . '" table/domain.');
			}

			$messages[$row['id']] = $row['message'];
		}

		return $messages;
	}

}
