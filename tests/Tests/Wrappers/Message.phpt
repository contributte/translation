<?php declare(strict_types = 1);

namespace Tests\Wrappers;

use Contributte;
use Tester;
use Tests;

$container = require __DIR__ . '/../../bootstrap.php';

class Message extends Tests\TestAbstract
{

	public function test01(): void
	{
		$message = new Contributte\Translation\Wrappers\Message('message', [], 'domain', 'locale');

		Tester\Assert::same('message', $message->getMessage());
		Tester\Assert::same([[], 'domain', 'locale'], $message->getParameters());

		$message->setMessage('new message')
			->setParameters([]);

		Tester\Assert::same('new message', $message->getMessage());
		Tester\Assert::same([], $message->getParameters());
	}

	public function test02(): void
	{
		$message = new Contributte\Translation\Wrappers\Message('message');

		Tester\Assert::same('message', $message->getMessage());
		Tester\Assert::same([], $message->getParameters());
	}

}

(new Message($container))->run();
