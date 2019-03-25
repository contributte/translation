<?php

/**
 * This file is part of the Translette\Translation
 */

declare(strict_types=1);

namespace Translette\Translation\Tests\Tests;

use Nette;
use Tester;
use Translette;

$container = require __DIR__ . '/../bootstrap.phpt';


/**
 * @author Ales Wita
 */
class Helpers extends Tester\TestCase
{
	/** @var Nette\DI\Container */
	private $container;


	/**
	 * @param Nette\DI\Container $container
	 */
	public function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
	}


	public function test01(): void
	{
		// whitelistRegexp
		Tester\Assert::null(Translette\Translation\Helpers::whitelistRegexp(null));
		Tester\Assert::same('~^(en)~i', Translette\Translation\Helpers::whitelistRegexp(['en']));
		Tester\Assert::same('~^(en|cz)~i', Translette\Translation\Helpers::whitelistRegexp(['en', 'cz']));
		Tester\Assert::same('~^(en|cz|sk)~i', Translette\Translation\Helpers::whitelistRegexp(['en', 'cz', 'sk']));

		// extractMessage
		Tester\Assert::same([null, null], Translette\Translation\Helpers::extractMessage(null));
		Tester\Assert::same([null, 'message'], Translette\Translation\Helpers::extractMessage('message'));
		Tester\Assert::same([null, 'message with space'], Translette\Translation\Helpers::extractMessage('message with space'));
		Tester\Assert::same(['domain', 'message'], Translette\Translation\Helpers::extractMessage('domain.message'));
		Tester\Assert::same([null, 'domain.message with space'], Translette\Translation\Helpers::extractMessage('domain.message with space'));
		Tester\Assert::same(['domain', 'long.message'], Translette\Translation\Helpers::extractMessage('domain.long.message'));
	}
}


$test = new Helpers($container);
$test->run();
