<?php declare(strict_types = 1);

/**
 * @skip
 */

namespace Tests\Loaders;

use Contributte\Translation\Loaders\NetteDatabase;
use Nette\Database\Connection;
use Nette\Localization\ITranslator;
use Tester\Assert;
use Tests\Helpers;
use Tests\TestAbstract;

$container = require __DIR__ . '/../../bootstrap.php';

final class NetteDatabaseTest extends TestAbstract
{

	public function test01(): void
	{
		$container = Helpers::createContainerFromConfigurator(
			$this->container->getParameters()['tempDir'],
			[
				'database' => [
					'dsn' => 'mysql:host=127.0.0.1;port=13306;dbname=test',
					'user' => 'root',
					'password' => '1234',
				],
				'translation' => [
					'loaders' => [
						'nettedatabase' => NetteDatabase::class,
					],
				],
			]
		);

		/** @var \Contributte\Translation\Translator $translator */
		$translator = $container->getByType(ITranslator::class);
		$connection = $container->getByType(Connection::class);

		$connection->query(file_get_contents(__DIR__ . '/../../sql.sql'));

		$translator->setLocale('cs');

		Assert::same('Ahoj', $translator->translate('db_table.hello'));

		$translator->setLocale('en');

		Assert::same('Hello', $translator->translate('db_table.hello'));
	}

}

(new NetteDatabaseTest($container))->run();
