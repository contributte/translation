<?php declare(strict_types = 1);

namespace Tests\Loaders;

use Contributte\Translation\Loaders\NetteDatabase;
use Contributte\Translation\Translator;
use Nette\Database\Connection;
use Nette\Database\ConnectionException;
use Nette\Localization\ITranslator;
use Tester\Assert;
use Tests\Helpers;
use Tests\TestAbstract;

$container = require __DIR__ . '/../../bootstrap.php';

final class NetteDatabaseTest extends TestAbstract
{

	private Translator $translator;

	private Connection $connection;

	protected function setUp()
	{
		parent::setUp();

		$container = Helpers::createContainerFromConfigurator(
			$this->container->getParameters()['tempDir'],
			[
				'database' => [
					'dsn' => 'mysql:host=127.0.0.1;port=13306;dbname=test',
					'user' => 'root',
					'password' => '1234',
					'options' => [
						'lazy' => true,
					],
				],
				'translation' => [
					'loaders' => [
						'nettedatabase' => NetteDatabase::class,
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
		$this->connection->query(file_get_contents(__DIR__ . '/../../sql.sql'));
		$this->translator->setLocale('cs_CZ');

		Assert::same('Ahoj', $this->translator->translate('db_table.hello'));

		$this->translator->setLocale('en_US');

		Assert::same('Hello', $this->translator->translate('db_table.hello'));
	}

}

(new NetteDatabaseTest($container))->run();
