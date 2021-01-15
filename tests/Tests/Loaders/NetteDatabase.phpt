<?php declare(strict_types = 1);

namespace Tests\Loaders;

use Contributte;
use Nette;
use Tester;
use Tests;

$container = require __DIR__ . '/../../bootstrap.php';

class NetteDatabase extends Tests\TestAbstract
{

	public function test01(): void
	{
		$container = Tests\Helpers::createContainerFromConfigurator(
			$this->container->getParameters()['tempDir'],
			[
				'database' => [
					'dsn' => 'mysql:host=127.0.0.1;port=13306;dbname=test',
					'user' => 'root',
					'password' => '1234',
				],
				'translation' => [
					'loaders' => [
						'nettedatabase' => Contributte\Translation\Loaders\NetteDatabase::class,
					],
				],
			]
		);

		/** @var Contributte\Translation\Translator $translator */
		$translator = $container->getByType(Nette\Localization\ITranslator::class);
		$connection = $container->getByType(Nette\Database\Connection::class);

		$connection->query(file_get_contents(__DIR__ . '/../../sql.sql'));

		$translator->setLocale('cs');

		Tester\Assert::same('Ahoj', $translator->translate('db_table.hello'));

		$translator->setLocale('en');

		Tester\Assert::same('Hello', $translator->translate('db_table.hello'));
	}

}

(new NetteDatabase($container))->run();
