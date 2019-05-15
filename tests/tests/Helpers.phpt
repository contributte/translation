<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation\Tests\Tests;

use Contributte;
use Tester;

$container = require __DIR__ . '/../bootstrap.php';


/**
 * @author Ales Wita
 */
class Helpers extends Contributte\Translation\Tests\AbstractTest
{
	public function test01(): void
	{
		// whitelistRegexp
		Tester\Assert::null(Contributte\Translation\Helpers::whitelistRegexp(null));
		Tester\Assert::same('~^(en)~i', Contributte\Translation\Helpers::whitelistRegexp(['en']));
		Tester\Assert::same('~^(en|cz)~i', Contributte\Translation\Helpers::whitelistRegexp(['en', 'cz']));
		Tester\Assert::same('~^(en|cz|sk)~i', Contributte\Translation\Helpers::whitelistRegexp(['en', 'cz', 'sk']));

		// extractMessage
		Tester\Assert::same([null, 'message'], Contributte\Translation\Helpers::extractMessage('message'));
		Tester\Assert::same([null, 'message with space'], Contributte\Translation\Helpers::extractMessage('message with space'));
		Tester\Assert::same(['domain', 'message'], Contributte\Translation\Helpers::extractMessage('domain.message'));
		Tester\Assert::same([null, 'domain.message with space'], Contributte\Translation\Helpers::extractMessage('domain.message with space'));
		Tester\Assert::same(['domain', 'long.message'], Contributte\Translation\Helpers::extractMessage('domain.long.message'));
		Tester\Assert::same(['domain', ''], Contributte\Translation\Helpers::extractMessage('domain.'));
		Tester\Assert::same(['', 'message'], Contributte\Translation\Helpers::extractMessage('.message'));
	}
}


(new Helpers($container))->run();
