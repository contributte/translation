<?php declare(strict_types=1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Contributte\Translation\Tests\Tests\Wrappers;

use Contributte;
use Tester;

$container = require __DIR__ . '/../../bootstrap.php';

class NotTranslate extends Contributte\Translation\Tests\AbstractTest
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
