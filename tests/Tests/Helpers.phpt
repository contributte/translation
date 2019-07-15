<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Tests;

use Contributte;
use Tester;
use Tests;

$container = require __DIR__ . '/../bootstrap.php';

class Helpers extends Tests\TestAbstract
{

	public function test01(): void
	{
		// whitelistRegexp
		Tester\Assert::null(Contributte\Translation\Helpers::whitelistRegexp(null));
		Tester\Assert::same('~^(en)~i', Contributte\Translation\Helpers::whitelistRegexp(['en']));
		Tester\Assert::same('~^(en|cz)~i', Contributte\Translation\Helpers::whitelistRegexp(['en', 'cz']));
		Tester\Assert::same('~^(en|cz|sk)~i', Contributte\Translation\Helpers::whitelistRegexp(['en', 'cz', 'sk']));

		// extractMessage
		Tester\Assert::same(['messages', 'message'], Contributte\Translation\Helpers::extractMessage('message'));
		Tester\Assert::same(['messages', 'message with space'], Contributte\Translation\Helpers::extractMessage('message with space'));
		Tester\Assert::same(['domain', 'message'], Contributte\Translation\Helpers::extractMessage('domain.message'));
		Tester\Assert::same(['messages', 'domain.message with space'], Contributte\Translation\Helpers::extractMessage('domain.message with space'));
		Tester\Assert::same(['domain', 'long.message'], Contributte\Translation\Helpers::extractMessage('domain.long.message'));
		Tester\Assert::same(['domain', ''], Contributte\Translation\Helpers::extractMessage('domain.'));
		Tester\Assert::same(['', 'message'], Contributte\Translation\Helpers::extractMessage('.message'));
	}

}

(new Helpers($container))->run();
