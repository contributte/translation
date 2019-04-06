<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation\Tests\Tests\Resource;

use Contributte;
use Tester;

$container = require __DIR__ . '/../../bootstrap.php';


/**
 * @author Ales Wita
 */
class Database extends Contributte\Translation\Tests\AbstractTest
{
	public function test01(): void
	{
		$resource = new Contributte\Translation\Resources\Database('resource', 10);

		Tester\Assert::same('resource', $resource->__toString());
		Tester\Assert::true($resource->isFresh(11));
		Tester\Assert::true($resource->isFresh(10));
		Tester\Assert::false($resource->isFresh(9));
	}
}


(new Database($container))->run();
