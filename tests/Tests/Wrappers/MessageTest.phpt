<?php declare(strict_types = 1);

namespace Tests\Wrappers;

use Contributte\Translation\Wrappers\Message;
use Tester\Assert;
use Tests\TestAbstract;

$container = require __DIR__ . '/../../bootstrap.php';

final class MessageTest extends TestAbstract
{

	public function test01(): void
	{
		$message = new Message('message', [], 'domain', 'locale');

		Assert::same('message', $message->getMessage());
		Assert::same([[], 'domain', 'locale'], $message->getParameters());

		$message->setMessage('new message')
			->setParameters([]);

		Assert::same('new message', $message->getMessage());
		Assert::same([], $message->getParameters());
	}

	public function test02(): void
	{
		$message = new Message('message');

		Assert::same('message', $message->getMessage());
		Assert::same([], $message->getParameters());
	}

}

(new MessageTest($container))->run();
