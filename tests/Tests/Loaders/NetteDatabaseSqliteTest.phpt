<?php declare(strict_types = 1);

namespace Tests\Loaders;

use Contributte\Translation\Loaders\NetteDatabase;
use Nette\Database\Connection;
use Tester\Assert;
use Tester\Environment;
use Tester\FileMock;
use Tests\TestAbstract;

$container = require __DIR__ . '/../../bootstrap.php';

if (!extension_loaded('pdo_sqlite')) {
	Environment::skip('Extension pdo_sqlite is not loaded');
}

final class NetteDatabaseSqliteTest extends TestAbstract
{

	public function test01(): void
	{
		$connection = new Connection('sqlite::memory:');
		$connection->query('CREATE TABLE "db_table" ("id" varchar(255) NOT NULL, "locale" varchar(255) NOT NULL, "message" varchar(255) NOT NULL)');
		$connection->query('INSERT INTO "db_table"', [
			['id' => 'hello', 'locale' => 'cs_CZ', 'message' => 'Ahoj'],
			['id' => 'hello', 'locale' => 'en_US', 'message' => 'Hello'],
		]);

		$queries = [];
		$connection->onQuery[] = static function (Connection $connection, $result) use (&$queries): void {
			$queries[] = $connection->getLastQueryString();
		};

		$loader = new NetteDatabase($connection);

		$catalogue = $loader->load(FileMock::create('', 'nettedatabase'), 'cs_CZ', 'db_table');
		Assert::same(['db_table' => ['hello' => 'Ahoj']], $catalogue->all());

		$catalogue = $loader->load(FileMock::create('', 'nettedatabase'), 'en_US', 'db_table');
		Assert::same(['db_table' => ['hello' => 'Hello']], $catalogue->all());

		Assert::count(2, $queries);

		foreach ($queries as $query) {
			Assert::notContains('`', (string) $query);
		}
	}

}

(new NetteDatabaseSqliteTest($container))->run();
