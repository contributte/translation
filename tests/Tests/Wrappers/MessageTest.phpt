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

		Assert::same('message', $message->message);
		Assert::same([[], 'domain', 'locale'], $message->parameters);

		$message->message = 'new message';
		$message->parameters = [];

		Assert::same('new message', $message->message);
		Assert::same([], $message->parameters);
	}

	public function test02(): void
	{
		$message = new Message('message');

		Assert::same('message', $message->message);
		Assert::same([], $message->parameters);
	}

}

(new MessageTest($container))->run();
