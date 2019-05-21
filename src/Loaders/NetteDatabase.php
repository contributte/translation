<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Contributte\Translation\Loaders;

use Contributte;
use Nette;
use stdClass;
use Symfony;

class NetteDatabase extends DatabaseAbstract implements Symfony\Component\Translation\Loader\LoaderInterface
{

	/** @var Nette\Database\Connection */
	private $connection;

	public function __construct(Nette\Database\Connection $connection)
	{
		$this->connection = $connection;
	}

	/**
	 * @return string[]
	 * @throws Contributte\Translation\Exceptions\InvalidState
	 */
	protected function getMessages(stdClass $config, string $resource, string $locale, string $domain): array
	{
		$messages = [];

		foreach ($this->connection->query('SELECT ? AS `id`, ? AS `locale`, ? AS `message` FROM ? WHERE ?', Nette\Database\Connection::literal($config->id), Nette\Database\Connection::literal($config->locale), Nette\Database\Connection::literal($config->message), Nette\Database\Connection::literal($config->table), Nette\Database\Connection::literal('?', [$config->locale => $locale]))->fetchAll() as $v1) {
			if (array_key_exists($v1['id'], $messages)) {
				throw new Contributte\Translation\Exceptions\InvalidState('Id "' . $v1['id'] . '" declared twice in "' . $config->table . '" table/domain.');
			}

			$messages[$v1['id']] = $v1['message'];
		}

		return $messages;
	}

}
