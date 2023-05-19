<?php declare(strict_types = 1);

namespace Tests\Loaders;

use Contributte\Translation\Loaders\NextrasDbal;
use Contributte\Translation\Translator;
use Nette\Localization\ITranslator;
use Nextras\Dbal\Connection;
use Nextras\Dbal\ConnectionException;
use Tester\Assert;
use Tests\Helpers;
use Tests\TestAbstract;

$container = require __DIR__ . '/../../bootstrap.php';

final class NextrasDbalTest extends TestAbstract
{

	private Translator $translator;

	private Connection $connection;

	protected function setUp()
	{
		parent::setUp();

		$container = Helpers::createContainerFromConfigurator(
			$this->container->getParameters()['tempDir'],
			[
				'extensions' => [
					'nextras.dbal' => 'Nextras\Dbal\Bridges\NetteDI\DbalExtension',
				],
				'nextras.dbal' => [
					'driver' => 'mysqli',
					'host' => '127.0.0.1',
					'port' => 13306,
					'database' => 'test',
					'username' => 'root',
					'password' => '1234',
					'connectionTz' => 'auto-offset',
				],
				'translation' => [
					'loaders' => [
						'nextrasdbal' => NextrasDbal::class,
					],
				],
			]
		);

		$this->translator = $container->getByType(ITranslator::class);
		$this->connection = $container->getByType(Connection::class);

		try {
			$this->connection->connect();
		} catch (ConnectionException $e) {
			$this->skip('Database not connected');
		}
	}

	public function test01(): void
	{
		$queries = file_get_contents(__DIR__ . '/../../sql.sql');
		$queries = explode(';', (string) $queries);
		$queries = array_filter($queries);

		foreach ($queries as $query) {
			$query = trim($query);
			if ($query) {
				$this->connection->query($query);
			}
		}

		$this->translator->setLocale('cs_CZ');

		Assert::same('Ahoj', $this->translator->translate('db_table.hello'));

		$this->translator->setLocale('en_US');

		Assert::same('Hello', $this->translator->translate('db_table.hello'));
	}

}

(new NextrasDbalTest($container))->run();
