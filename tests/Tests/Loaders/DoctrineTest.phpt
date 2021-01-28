<?php declare(strict_types = 1);

namespace Tests\Loaders;

use Contributte\Translation\Exceptions\InvalidArgument;
use Contributte\Translation\Exceptions\InvalidState;
use Contributte\Translation\Loaders\Doctrine;
use Contributte\Translation\Loaders\NetteDatabase;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityRepository;
use Mockery;
use Nette\Database\Connection;
use Symfony\Component\Translation\MessageCatalogue;
use Tester\Assert;
use Tester\FileMock;
use Tests\TestAbstract;

$container = require __DIR__ . '/../../bootstrap.php';

final class DoctrineTest extends TestAbstract
{

	public function test01(): void
	{
		Assert::true($this->createCatalogue(FileMock::create(), 'en_US', 'messages', 'id', 'locale', 'message') instanceof MessageCatalogue);
		Assert::true($this->createCatalogue(FileMock::create('table: "my_table"'), 'en_US', 'my_table', 'id', 'locale', 'message') instanceof MessageCatalogue);
		Assert::true($this->createCatalogue(FileMock::create('id: "my_id"'), 'en_US', 'messages', 'my_id', 'locale', 'message') instanceof MessageCatalogue);
		Assert::true($this->createCatalogue(FileMock::create('locale: "my_locale"'), 'en_US', 'messages', 'id', 'my_locale', 'message') instanceof MessageCatalogue);
		Assert::true($this->createCatalogue(FileMock::create('message: "my_message"'), 'en_US', 'messages', 'id', 'locale', 'my_message') instanceof MessageCatalogue);
		Assert::exception(static function (): void {
			(new NetteDatabase(Mockery::mock(Connection::class)))->load('unknown_file', 'en_US');
		}, InvalidArgument::class, 'Something wrong with resource file "unknown_file".');

		$catalogue = $this->createCatalogue(FileMock::create(), 'en_US', 'messages', 'id', 'locale', 'message', [(object) ['id' => 'my.id', 'message' => 'my message'], (object) ['id' => 'hi', 'message' => 'Hi']]);

		Assert::same(['messages'], $catalogue->getDomains());
		Assert::true($catalogue->has('my.id', 'messages'));
		Assert::same('my message', $catalogue->get('my.id', 'messages'));
		Assert::true($catalogue->has('hi', 'messages'));
		Assert::same('Hi', $catalogue->get('hi', 'messages'));

		Assert::exception(function (): void {
			$this->createCatalogue(FileMock::create(), 'en_US', 'messages', 'id', 'locale', 'message', [(object) ['id' => 'duplicity.id', 'message' => 'my message'], (object) ['id' => 'duplicity.id', 'message' => 'my message']]);
		}, InvalidState::class, 'Id "duplicity.id" declared twice in "messages" table/domain.');
	}

	/**
	 * @param array<array<string>> $data
	 */
	private function createCatalogue(
		string $file,
		string $locale,
		string $table,
		string $columnId,
		string $columnLocale,
		string $columnMessage,
		array $data = []
	): MessageCatalogue
	{
		$emMock = Mockery::mock(EntityManagerDecorator::class);
		$repositoryMock = Mockery::mock(EntityRepository::class);

		$loader = new Doctrine($emMock);

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

(new DoctrineTest($container))->run();
