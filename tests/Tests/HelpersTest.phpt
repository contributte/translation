<?php declare(strict_types = 1);

namespace Tests;

use Contributte\Translation\Helpers;
use Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';

final class HelpersTest extends TestAbstract
{

	public function test01(): void
	{
		// whitelistRegexp
		Assert::null(Helpers::whitelistRegexp(null));
		Assert::same('~^(en)~i', Helpers::whitelistRegexp(['en']));
		Assert::same('~^(en|cz)~i', Helpers::whitelistRegexp(['en', 'cz']));
		Assert::same('~^(en|cz|sk)~i', Helpers::whitelistRegexp(['en', 'cz', 'sk']));

		// extractMessage
		Assert::same(['messages', 'message'], Helpers::extractMessage('message'));
		Assert::same(['messages', 'message with space'], Helpers::extractMessage('message with space'));
		Assert::same(['domain', 'message'], Helpers::extractMessage('domain.message'));
		Assert::same(['domain', 'message with space'], Helpers::extractMessage('domain.message with space'));
		Assert::same(['domain', 'long.message'], Helpers::extractMessage('domain.long.message'));
		Assert::same(['domain', ''], Helpers::extractMessage('domain.'));
		Assert::same(['', 'message'], Helpers::extractMessage('.message'));
		Assert::same(['domain', 'Some sentense.'], Helpers::extractMessage('domain.Some sentense.'));
		Assert::same(['messages', 'domain .some_sentense'], Helpers::extractMessage('domain .some_sentense'));
	}

}

(new HelpersTest($container))->run();
