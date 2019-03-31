<?php

/**
 * This file is part of the Translette/Translation
 */

declare(strict_types=1);

namespace Translette\Translation\Tests\Tests\Loaders;

use Symfony\Component\Translation\Tests\DependencyInjection\TranslationExtractorPassTest;
use Tester;
use Translette;

$container = require __DIR__ . '/../../bootstrap.php';


/**
 * @author Ales Wita
 */
class Neon extends Translette\Translation\Tests\AbstractTest
{
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


	public function test02(): void
	{
		Tester\Assert::exception(function (): void {(new Translette\Translation\Loaders\Neon)->load('unknown_file', 'en');}, Translette\Translation\InvalidArgumentException::class, 'Something wrong with resource file "unknown_file".');
	}
}


(new Neon($container))->run();
