<?php declare(strict_types = 1);

namespace Tests\Wrappers;

use Contributte\Translation\Wrappers\NotTranslate;
use Tester\Assert;
use Tests\TestAbstract;

$container = require __DIR__ . '/../../bootstrap.php';

final class NotTranslateTest extends TestAbstract
{

	public function test01(): void
	{
		$message = new NotTranslate('message');

		Assert::same('message', $message->message);

		$message->message = 'new message';

		Assert::same('new message', $message->message);
	}

}

(new NotTranslateTest($container))->run();
