<?php

/**
 * This file is part of the Contributte/Translation
 *
 * @skip
 */

declare(strict_types=1);

namespace Contributte\Translation\Tests\Tests\Wrappers;

use Contributte;
use Tester;

$container = require __DIR__ . '/../../bootstrap.php';


/**
 * @author Ales Wita
 */
class Message extends Contributte\Translation\Tests\AbstractTest
{
	public function test01(): void
	{
		$message = new Contributte\Translation\Wrappers\Message('message', [], 'domain', 'locale');

		Tester\Assert::same('message', $message->message);
		Tester\Assert::same([[], 'domain', 'locale'], $message->parameters);

		$message->setMessage('new message')
			->setParameters([]);

		Tester\Assert::same('new message', $message->message);
		Tester\Assert::same([], $message->parameters);
	}


	public function test02(): void
	{
		$message = new Contributte\Translation\Wrappers\Message('message');

		Tester\Assert::same('message', $message->message);
		Tester\Assert::same([], $message->parameters);
	}
}


(new Message($container))->run();
