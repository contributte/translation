<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation\Tests\Tests\Wrappers;

use Contributte;
use Tester;

$container = require __DIR__ . '/../../bootstrap.php';


/**
 * @author Ales Wita
 */
class NotTranslate extends Contributte\Translation\Tests\AbstractTest
{
	public function test01(): void
	{
		$message = new Contributte\Translation\Wrappers\Message('message');

		Tester\Assert::same('message', $message->message);

		$message->setMessage('new message');

		Tester\Assert::same('new message', $message->message);
	}
}


(new NotTranslate($container))->run();
