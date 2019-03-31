<?php

/**
 * This file is part of the Translette/Translation
 */

declare(strict_types=1);

namespace Translette\Translation\Tests\Tests;

use Nette;
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
		Tester\Assert::same([], $translator->prefix);
		Tester\Assert::same('', $translator->formattedPrefix);
		Tester\Assert::same([], $translator->availableLocales);
		Tester\Assert::same('en', $translator->locale);

		new Translette\Translation\Tracy\Panel($translator);

		Tester\Assert::true($translator->tracyPanel instanceof Translette\Translation\Tracy\Panel);

		$translator->setLocalesWhitelist(['en', 'cs']);

		Tester\Assert::same(['en', 'cs'], $translator->localesWhitelist);

		$translator->setPrefix(['prefix']);

		Tester\Assert::same('prefix', $translator->formattedPrefix);

		$translator->addPrefix('next');

		Tester\Assert::same('prefix.next', $translator->formattedPrefix);

		$translator->removePrefix();

		Tester\Assert::same('prefix', $translator->formattedPrefix);

		$translator->setPrefix([]);

		Tester\Assert::exception(function () use ($translator): void {$translator->removePrefix();}, Translette\Translation\InvalidArgumentException::class, 'Can not remove empty prefix.');
		Tester\Assert::exception(function () use ($translator): void {$translator->removePrefix('unknown');}, Translette\Translation\InvalidArgumentException::class, 'Unknown "unknown" prefix.');


		$translator->addResource('neon', __DIR__ . '/file.neon', 'en_US', 'domain');
		$translator->addResource('neon', __DIR__ . '/file.neon', 'cs_CZ', 'domain');

		Tester\Assert::same(['cs_CZ', 'en_US'], $translator->availableLocales);
	}


	public function test02(): void
	{
		$configurator = new Nette\Configurator;

		$configurator->setTempDirectory($this->container->getParameters()['tempDir'])
			->addConfig([
				'extensions' => [
					'translation' => Translette\Translation\DI\TranslationExtension::class,
				],
				'translation' => [
					'debug' => true,
					'locales' => [
						'default' => 'en',
					],
					'dirs' => [
						__DIR__ . '/../lang',
					],
				],
			]);

		$container = $configurator->createContainer();

		/** @var Translette\Translation\Translator $translator */
		$translator = $container->getByType(Nette\Localization\ITranslator::class);


		Tester\Assert::same('Hello', $translator->translate('messages.hello'));
		Tester\Assert::same('Hello', $translator->translate('hello'));
		Tester\Assert::same('Hello', $translator->translate('//messages.hello'));
		Tester\Assert::same('Hello', $translator->translate('hello', [], 'messages', 'en'));
		Tester\Assert::same('Hello', $translator->translate('hello', null, [], 'messages', 'en'));
		Tester\Assert::same('Hi Ales!', $translator->translate('messages.hi', ['name' => 'Ales']));
		Tester\Assert::same('Hi Ales!', $translator->translate('hi', ['name' => 'Ales']));
		Tester\Assert::same('Hi Ales!', $translator->translate('//messages.hi', ['name' => 'Ales']));
		Tester\Assert::same('Hi Ales!', $translator->translate('hi', ['name' => 'Ales'], 'messages', 'en'));
		Tester\Assert::same('Hi Ales!', $translator->translate('hi', null, ['name' => 'Ales'], 'messages', 'en'));
		Tester\Assert::same('There are no apples', $translator->translate('messages.apples', 0));
		Tester\Assert::same('There are no apples', $translator->translate('messages.apples', ['count' => 0]));
		Tester\Assert::same('There is one apple', $translator->translate('messages.apples', 1));
		Tester\Assert::same('There is one apple', $translator->translate('messages.apples', ['count' => 1]));
		Tester\Assert::same('There are 2 apples', $translator->translate('messages.apples', 2));
		Tester\Assert::same('There are 2 apples', $translator->translate('messages.apples', ['count' => 2]));
		Tester\Assert::same('Depth message', $translator->translate('messages.depth.message'));
		Tester\Assert::same('missing.translation', $translator->translate('messages.missing.translation'));

		$translator->addPrefix('messages');

		Tester\Assert::same('Hello', $translator->translate('hello'));
		Tester\Assert::same('Hello', $translator->translate('//messages.hello'));
		Tester\Assert::same('messages.hello', $translator->translate('messages.hello'));
		Tester\Assert::same('Hi Ales!', $translator->translate('hi', ['name' => 'Ales']));
		Tester\Assert::same('Hi Ales!', $translator->translate('//messages.hi', ['name' => 'Ales']));
		Tester\Assert::same('messages.hi', $translator->translate('messages.hi', ['name' => 'Ales']));

		$translator->removePrefix();

		$prefixedTranslator = $translator->domain('messages');
		Tester\Assert::same('Hello', $prefixedTranslator->translate('hello'));
	}
}


(new Translator($container))->run();
