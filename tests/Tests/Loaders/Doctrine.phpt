<?php declare(strict_types = 1);

namespace Tests\Loaders;

use Contributte;
use Doctrine as Dctr;
use Mockery;
use Nette;
use Symfony;
use Tester\Assert;
use Tester\FileMock;
use Tests;

$container = require __DIR__ . '/../../bootstrap.php';

class Doctrine extends Tests\TestAbstract
{

	public function test01(): void
	{
		Assert::true($this->createCatalogue(FileMock::create(), 'en_US', 'messages', 'id', 'locale', 'message') instanceof Symfony\Component\Translation\MessageCatalogue);
		Assert::true($this->createCatalogue(FileMock::create('table: "my_table"'), 'en_US', 'my_table', 'id', 'locale', 'message') instanceof Symfony\Component\Translation\MessageCatalogue);
		Assert::true($this->createCatalogue(FileMock::create('id: "my_id"'), 'en_US', 'messages', 'my_id', 'locale', 'message') instanceof Symfony\Component\Translation\MessageCatalogue);
		Assert::true($this->createCatalogue(FileMock::create('locale: "my_locale"'), 'en_US', 'messages', 'id', 'my_locale', 'message') instanceof Symfony\Component\Translation\MessageCatalogue);
		Assert::true($this->createCatalogue(FileMock::create('message: "my_message"'), 'en_US', 'messages', 'id', 'locale', 'my_message') instanceof Symfony\Component\Translation\MessageCatalogue);
		Assert::exception(function (): void {
			(new Contributte\Translation\Loaders\NetteDatabase(Mockery::mock(Nette\Database\Connection::class)))->load('unknown_file', 'en_US');
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'Something wrong with resource file "unknown_file".');

		$catalogue = $this->createCatalogue(FileMock::create(), 'en_US', 'messages', 'id', 'locale', 'message', [(object) ['id' => 'my.id', 'message' => 'my message'], (object) ['id' => 'hi', 'message' => 'Hi']]);

		Assert::same(['messages'], $catalogue->getDomains());
		Assert::true($catalogue->has('my.id', 'messages'));
		Assert::same('my message', $catalogue->get('my.id', 'messages'));
		Assert::true($catalogue->has('hi', 'messages'));
		Assert::same('Hi', $catalogue->get('hi', 'messages'));

		Assert::exception(function (): void {
			$this->createCatalogue(FileMock::create(), 'en_US', 'messages', 'id', 'locale', 'message', [(object) ['id' => 'duplicity.id', 'message' => 'my message'], (object) ['id' => 'duplicity.id', 'message' => 'my message']]);
		}, Contributte\Translation\Exceptions\InvalidState::class, 'Id "duplicity.id" declared twice in "messages" table/domain.');
	}

	/**
	 * @param string[][] $data
	 * @internal
	 */
	private function createCatalogue(string $file, string $locale, string $table, string $columnId, string $columnLocale, string $columnMessage, array $data = []): Symfony\Component\Translation\MessageCatalogue
	{
		$emMock = Mockery::mock(Dctr\ORM\Decorator\EntityManagerDecorator::class);
		$repositoryMock = Mockery::mock(Dctr\ORM\EntityRepository::class);

		$loader = new Contributte\Translation\Loaders\Doctrine($emMock);

		$emMock->shouldReceive('getRepository')
			->once()
			->withArgs([$table])
			->andReturn($repositoryMock);

		$repositoryMock->shouldReceive('findBy')
			->once()
			->withArgs([[$columnLocale => $locale]])
			->andReturn($data);

		return $loader->load($file, $locale, $table);
	}

}

(new Doctrine($container))->run();
