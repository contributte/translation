<?php declare(strict_types = 1);

namespace Contributte\Translation\Loaders;

use Contributte\Translation\Exceptions\InvalidState;
use Nette\Database\Connection;
use stdClass;
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

		foreach ($this->connection->query(
			'SELECT ? AS `id`, ? AS `locale`, ? AS `message` FROM ? WHERE ?',
			Connection::literal($config->id),
			Connection::literal($config->locale),
			Connection::literal($config->message),
			Connection::literal($config->table),
			Connection::literal('?', [$config->locale => $locale])
		)->fetchAll() as $v1) {
			if (array_key_exists($v1['id'], $messages)) {
				throw new InvalidState('Id "' . $v1['id'] . '" declared twice in "' . $config->table . '" table/domain.');
			}

			$messages[$v1['id']] = $v1['message'];
		}

		return $messages;
	}

}
