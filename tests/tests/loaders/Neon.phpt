<?php

/**
 * This file is part of the Translette/Translation
 */

declare(strict_types=1);

namespace Translette\Translation\Tests\Tests;

use Nette;
use Tester;
use Translette;

$container = require __DIR__ . '/../../bootstrap.php';


/**
 * @author Ales Wita
 */
class Neon extends Tester\TestCase
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
		$file = Tester\FileMock::create('
test:
	for: "translate"');


		$catalogue = (new Translette\Translation\Loaders\Neon)->load($file, 'en');

		Tester\Assert::true($catalogue instanceof \Symfony\Component\Translation\MessageCatalogue);
		Tester\Assert::same('en', $catalogue->getLocale());
		Tester\Assert::same(['messages'], $catalogue->getDomains());
		Tester\Assert::same('translate', $catalogue->get('test.for'));
		Tester\Assert::same('missing.translate', $catalogue->get('missing.translate'));
		Tester\Assert::same('missing.translate', $catalogue->get('missing.translate', 'domain'));


		$catalogue = (new Translette\Translation\Loaders\Neon)->load($file, 'cs', 'domain');
		Tester\Assert::same('cs', $catalogue->getLocale());
		Tester\Assert::same(['domain'], $catalogue->getDomains());
		Tester\Assert::same('translate', $catalogue->get('test.for', 'domain'));
		Tester\Assert::same('missing.translate', $catalogue->get('missing.translate', 'domain'));
		Tester\Assert::same('missing.translate', $catalogue->get('missing.translate'));
	}
}


$test = new Neon($container);
$test->run();
