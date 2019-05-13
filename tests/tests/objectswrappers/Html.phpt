<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation\Tests\Tests\ObjectsWrappers;

use Contributte;
use Nette;
use Tester;

$container = require __DIR__ . '/../../bootstrap.php';


/**
 * @author Ales Wita
 */
class Html extends Contributte\Translation\Tests\AbstractTest
{
	public function test01(): void
	{
		$object = Nette\Utils\Html::el()->setText('message');
		$wrapper = new Contributte\Translation\ObjectsWrappers\Html($object);

		Tester\Assert::same('message', $wrapper->getMessage());

		$wrapper->setMessage('new message');

		Tester\Assert::same('new message', $wrapper->getMessage());
		Tester\Assert::same('new message', $object->getText());
	}
}


(new Html($container))->run();
