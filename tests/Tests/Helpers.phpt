<?php declare(strict_types = 1);

namespace Tests;

use Contributte;
use Tester\Assert;
use Tests;

$container = require __DIR__ . '/../bootstrap.php';

class Helpers extends Tests\TestAbstract
{

	public function test01(): void
	{
		// whitelistRegexp
		Assert::null(Contributte\Translation\Helpers::whitelistRegexp(null));
		Assert::same('~^(en)~i', Contributte\Translation\Helpers::whitelistRegexp(['en']));
		Assert::same('~^(en|cz)~i', Contributte\Translation\Helpers::whitelistRegexp(['en', 'cz']));
		Assert::same('~^(en|cz|sk)~i', Contributte\Translation\Helpers::whitelistRegexp(['en', 'cz', 'sk']));

		// extractMessage
		Assert::same(['messages', 'message'], Contributte\Translation\Helpers::extractMessage('message'));
		Assert::same(['messages', 'message with space'], Contributte\Translation\Helpers::extractMessage('message with space'));
		Assert::same(['domain', 'message'], Contributte\Translation\Helpers::extractMessage('domain.message'));
		Assert::same(['domain', 'message with space'], Contributte\Translation\Helpers::extractMessage('domain.message with space'));
		Assert::same(['domain', 'long.message'], Contributte\Translation\Helpers::extractMessage('domain.long.message'));
		Assert::same(['domain', ''], Contributte\Translation\Helpers::extractMessage('domain.'));
		Assert::same(['', 'message'], Contributte\Translation\Helpers::extractMessage('.message'));
		Assert::same(['domain', 'Some sentense.'], Contributte\Translation\Helpers::extractMessage('domain.Some sentense.'));
		Assert::same(['messages', 'domain .some_sentense'], Contributte\Translation\Helpers::extractMessage('domain .some_sentense'));
	}

}

(new Helpers($container))->run();
