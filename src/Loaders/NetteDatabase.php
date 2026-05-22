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
			$id = $row['id'];
			$message = $row['message'];

			if (!is_string($id) && !is_int($id)) {
				throw new InvalidState('Id column "' . $config['id'] . '" must be string or int, ' . gettype($id) . ' given.');
			}

			if (!is_string($message)) {
				throw new InvalidState('Message column "' . $config['message'] . '" must be string, ' . gettype($message) . ' given.');
			}

			if (array_key_exists($id, $messages)) {
				throw new InvalidState('Id "' . $id . '" declared twice in "' . $config['table'] . '" table/domain.');
			}

			$messages[$id] = $message;
		}

		return $messages;
	}

}
