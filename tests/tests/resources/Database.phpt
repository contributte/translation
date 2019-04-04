<?php

/**
 * This file is part of the Translette/Translation
 */

declare(strict_types=1);

namespace Translette\Translation\Tests\Tests\Resource;

use Tester;
use Translette;

$container = require __DIR__ . '/../../bootstrap.php';


/**
 * @author Ales Wita
 */
class Database extends Translette\Translation\Tests\AbstractTest
{
	public function test01(): void
	{
		$resource = new Translette\Translation\Resources\Database('resource', 10);

		Tester\Assert::same('resource', $resource->__toString());
		Tester\Assert::true($resource->isFresh(11));
		Tester\Assert::true($resource->isFresh(10));
		Tester\Assert::false($resource->isFresh(9));
	}
}


(new Database($container))->run();
