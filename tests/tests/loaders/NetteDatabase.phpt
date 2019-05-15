<?php

/**
 * This file is part of the Contributte/Translation
 *
 * @skip
 */

declare(strict_types=1);

namespace Contributte\Translation\Tests\Tests\Loaders;

use Contributte;
use Symfony;
use Tester;

$container = require __DIR__ . '/../../bootstrap.php';


/**
 * @author Ales Wita
 */
class NetteDatabase extends Contributte\Translation\Tests\AbstractTest
{
	public function test01(): void
	{
		Tester\Assert::true($this->createCatalogue(Tester\FileMock::create(), 'en_US', 'messages', 'id', 'locale', 'message') instanceof Symfony\Component\Translation\MessageCatalogue);
		Tester\Assert::true($this->createCatalogue(Tester\FileMock::create('table: "my_table"'), 'en_US', 'my_table', 'id', 'locale', 'message') instanceof Symfony\Component\Translation\MessageCatalogue);
		Tester\Assert::true($this->createCatalogue(Tester\FileMock::create('id: "my_id"'), 'en_US', 'messages', 'my_id', 'locale', 'message') instanceof Symfony\Component\Translation\MessageCatalogue);
		Tester\Assert::true($this->createCatalogue(Tester\FileMock::create('locale: "my_locale"'), 'en_US', 'messages', 'id', 'my_locale', 'message') instanceof Symfony\Component\Translation\MessageCatalogue);
		Tester\Assert::true($this->createCatalogue(Tester\FileMock::create('message: "my_message"'), 'en_US', 'messages', 'id', 'locale', 'my_message') instanceof Symfony\Component\Translation\MessageCatalogue);
		Tester\Assert::exception(function (): void {(new Contributte\Translation\Loaders\NetteDatabase(\Mockery::mock(\Nette\Database\Connection::class)))->load('unknown_file', 'en_US');}, Contributte\Translation\InvalidArgumentException::class, 'Something wrong with resource file "unknown_file".');


		$catalogue = $this->createCatalogue(Tester\FileMock::create(), 'en_US', 'messages', 'id', 'locale', 'message', [['id' => 'my.id', 'message' => 'my message'], ['id' => 'hi', 'message' => 'Hi']]);

		Tester\Assert::same(['messages'], $catalogue->getDomains());
		Tester\Assert::true($catalogue->has('my.id', 'messages'));
		Tester\Assert::same('my message', $catalogue->get('my.id', 'messages'));
		Tester\Assert::true($catalogue->has('hi', 'messages'));
		Tester\Assert::same('Hi', $catalogue->get('hi', 'messages'));
	}


	/**
	 * @internal
	 *
	 * @param string $file
	 * @param string $locale
	 * @param string $table
	 * @param string $columnId
	 * @param string $columnLocale
	 * @param string $columnMessage
	 * @param array $data
	 * @return Symfony\Component\Translation\MessageCatalogue
	 */
	private function createCatalogue(string $file, string $locale, string $table, string $columnId, string $columnLocale, string $columnMessage, array $data = []): Symfony\Component\Translation\MessageCatalogue
	{
		$connectionMock = \Mockery::mock(\Nette\Database\Connection::class);
		$resultSetMock = \Mockery::mock(\Nette\Database\ResultSet::class);

		$loader = new Contributte\Translation\Loaders\NetteDatabase($connectionMock);

		$connectionMock->shouldReceive('literal')
			->once()
			->withArgs([$columnId]);

		$connectionMock->shouldReceive('literal')
			->once()
			->withArgs([$columnLocale]);

		$connectionMock->shouldReceive('literal')
			->once()
			->withArgs([$columnMessage]);

		$connectionMock->shouldReceive('literal')
			->once()
			->withArgs([$table]);

		$connectionMock->shouldReceive('literal')
			->once()
			->withArgs(['?', [$columnLocale => $locale]]);

		$connectionMock->shouldReceive('query')
			->once()
			->andReturn($resultSetMock);

		$resultSetMock->shouldReceive('fetchAll')
			->once()
			->withNoArgs()
			->andReturn($data);

		return $loader->load($file, $locale, $table);
	}
}


(new NetteDatabase($container))->run();
