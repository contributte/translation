<?php declare(strict_types = 1);

namespace Tests\Loaders;

use Contributte\Translation\Loaders\NetteDatabase;
use Nette\Database\Connection;
use Nette\Database\ConnectionException;
use Tester\Assert;
use Tester\Environment;
use Tester\FileMock;
use Tests\TestAbstract;

$container = require __DIR__ . '/../../bootstrap.php';

if (!extension_loaded('pdo_pgsql')) {
	Environment::skip('Extension pdo_pgsql is not loaded');
}

try {
	(new Connection('pgsql:host=127.0.0.1;port=15432;dbname=test', 'postgres', '1234'))->connect();
} catch (ConnectionException $e) {
	Environment::skip('Database not connected');
}

final class NetteDatabasePostgreSqlTest extends TestAbstract
{

	public function test01(): void
	{
		$connection = new Connection('pgsql:host=127.0.0.1;port=15432;dbname=test', 'postgres', '1234');
		$connection->query('DROP TABLE IF EXISTS "db_table"');
		$connection->query('CREATE TABLE "db_table" ("id" varchar(255) NOT NULL, "locale" varchar(255) NOT NULL, "message" varchar(255) NOT NULL)');
		$connection->query('INSERT INTO "db_table"', [
			['id' => 'hello', 'locale' => 'cs_CZ', 'message' => 'Ahoj'],
			['id' => 'hello', 'locale' => 'en_US', 'message' => 'Hello'],
		]);

		$loader = new NetteDatabase($connection);

		$catalogue = $loader->load(FileMock::create('', 'nettedatabase'), 'cs_CZ', 'db_table');
		Assert::same(['db_table' => ['hello' => 'Ahoj']], $catalogue->all());

		$catalogue = $loader->load(FileMock::create('', 'nettedatabase'), 'en_US', 'db_table');
		Assert::same(['db_table' => ['hello' => 'Hello']], $catalogue->all());

		$connection->query('DROP TABLE "db_table"');
	}

}

(new NetteDatabasePostgreSqlTest($container))->run();
