<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

$container = require __DIR__ . '/../../bootstrap.php';

class NotTranslate extends AbstractTest
{

	public function test01(): void
	{
		$message = new Contributte\Translation\Wrappers\NotTranslate('message');

		Tester\Assert::same('message', $message->message);

		$message->setMessage('new message');

		Tester\Assert::same('new message', $message->message);
	}

}


(new NotTranslate($container))->run();
