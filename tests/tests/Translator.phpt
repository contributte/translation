<?php

/**
 * This file is part of the Translette/Translation
 */

declare(strict_types=1);

namespace Translette\Translation\Tests\Tests;

use Tester;
use Translette;

$container = require __DIR__ . '/../bootstrap.php';


/**
 * @author Ales Wita
 */
class Translator extends Translette\Translation\Tests\AbstractTest
{
	public function test01(): void
	{
		$translator = new Translette\Translation\Translator(new Translette\Translation\LocaleResolver, new Translette\Translation\FallbackResolver, 'en', __DIR__ . '/cacheDir', true);

		Tester\Assert::true($translator->localeResolver instanceof Translette\Translation\LocaleResolver);
		Tester\Assert::true($translator->fallbackResolver instanceof Translette\Translation\FallbackResolver);
		Tester\Assert::same('en', $translator->defaultLocale);
		Tester\Assert::same(__DIR__ . '/cacheDir', $translator->cacheDir);
		Tester\Assert::true($translator->debug);
		Tester\Assert::null($translator->tracyPanel);
		Tester\Assert::null($translator->localesWhitelist);
		Tester\Assert::null($translator->domain);
		Tester\Assert::same([], $translator->availableLocales);
		Tester\Assert::same('en', $translator->locale);

		new Translette\Translation\Tracy\Panel($translator);

		Tester\Assert::true($translator->tracyPanel instanceof Translette\Translation\Tracy\Panel);

		$translator->setLocalesWhitelist(['en', 'cs']);

		Tester\Assert::same(['en', 'cs'], $translator->localesWhitelist);

		$translator->setDomain('domain');

		Tester\Assert::same('domain', $translator->domain);

		$translator->addResource('neon', __DIR__ . '/file.neon', 'en_US', 'domain');
		$translator->addResource('neon', __DIR__ . '/file.neon', 'cs_CZ', 'domain');

		Tester\Assert::same(['cs_CZ', 'en_US'], $translator->availableLocales);
	}
}


(new Translator($container))->run();
