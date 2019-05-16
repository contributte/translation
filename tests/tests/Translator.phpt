<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation\Tests\Tests;

use Contributte;
use Nette;
use Tester;

$container = require __DIR__ . '/../bootstrap.php';


/**
 * @author Ales Wita
 */
class Translator extends Contributte\Translation\Tests\AbstractTest
{
	public function test01(): void
	{
		$translator = new Contributte\Translation\Translator(new Contributte\Translation\LocaleResolver, new Contributte\Translation\FallbackResolver, 'en', __DIR__ . '/cacheDir', true);

		Tester\Assert::true($translator->localeResolver instanceof Contributte\Translation\LocaleResolver);
		Tester\Assert::true($translator->fallbackResolver instanceof Contributte\Translation\FallbackResolver);
		Tester\Assert::same('en', $translator->defaultLocale);
		Tester\Assert::same(__DIR__ . '/cacheDir', $translator->cacheDir);
		Tester\Assert::true($translator->debug);
		Tester\Assert::null($translator->tracyPanel);
		Tester\Assert::null($translator->localesWhitelist);
		Tester\Assert::same([], $translator->prefix);
		Tester\Assert::same('', $translator->formattedPrefix);
		Tester\Assert::same([], $translator->availableLocales);
		Tester\Assert::same('en', $translator->locale);

		new Contributte\Translation\Tracy\Panel($translator);

		Tester\Assert::true($translator->tracyPanel instanceof Contributte\Translation\Tracy\Panel);

		$translator->setLocalesWhitelist(['en', 'cs']);

		Tester\Assert::same(['en', 'cs'], $translator->localesWhitelist);

		$translator->setPrefix(['prefix']);

		Tester\Assert::same('prefix', $translator->formattedPrefix);

		$translator->addPrefix('next');

		Tester\Assert::same('prefix.next', $translator->formattedPrefix);

		$translator->removePrefix();

		Tester\Assert::same('prefix', $translator->formattedPrefix);

		$translator->setPrefix([]);

		Tester\Assert::exception(function () use ($translator): void {$translator->removePrefix();}, Contributte\Translation\InvalidArgumentException::class, 'Can not remove empty prefix.');
		Tester\Assert::exception(function () use ($translator): void {$translator->removePrefix('unknown');}, Contributte\Translation\InvalidArgumentException::class, 'Unknown "unknown" prefix.');


		$translator->addResource('neon', __DIR__ . '/file.neon', 'en_US', 'domain');
		$translator->addResource('neon', __DIR__ . '/file.neon', 'cs_CZ', 'domain');

		Tester\Assert::same(['cs_CZ', 'en_US'], $translator->availableLocales);
	}


	public function test02(): void
	{
		$container = $this->createContainer();

		/** @var Contributte\Translation\Translator $translator */
		$translator = $container->getByType(Nette\Localization\ITranslator::class);


		Tester\Assert::throws(function () use ($translator): void {$translator->translate(new \stdClass);}, Contributte\Translation\InvalidArgumentException::class, 'Message must be string, object given.');
		Tester\Assert::same('', $translator->translate(null));
		Tester\Assert::same('0', $translator->translate(0));
		Tester\Assert::same('1', $translator->translate(1));
		Tester\Assert::same('Not translate!', $translator->translate(new Contributte\Translation\Wrappers\NotTranslate('Not translate!')));
		Tester\Assert::same('Hello', $translator->translate('messages.hello'));
		Tester\Assert::same('Hello', $translator->translate(new Contributte\Translation\Wrappers\Message('messages.hello')));
		Tester\Assert::same('Hello', $translator->translate('hello'));
		Tester\Assert::same('Hello', $translator->translate(new Contributte\Translation\Wrappers\Message('hello')));
		Tester\Assert::same('Hello', $translator->translate('//messages.hello'));
		Tester\Assert::same('Hello', $translator->translate(new Contributte\Translation\Wrappers\Message('//messages.hello')));
		Tester\Assert::same('Hello', $translator->translate('hello', [], 'messages', 'en'));
		Tester\Assert::same('Hello', $translator->translate(new Contributte\Translation\Wrappers\Message('hello', [], 'messages', 'en')));
		Tester\Assert::same('Hello', $translator->translate('hello', null, [], 'messages', 'en'));
		Tester\Assert::same('Hello', $translator->translate(new Contributte\Translation\Wrappers\Message('hello', null, [], 'messages', 'en')));
		Tester\Assert::same('Hi Ales!', $translator->translate('messages.hi', ['name' => 'Ales']));
		Tester\Assert::same('Hi Ales!', $translator->translate(new Contributte\Translation\Wrappers\Message('messages.hi', ['name' => 'Ales'])));
		Tester\Assert::same('Hi Ales!', $translator->translate('hi', ['name' => 'Ales']));
		Tester\Assert::same('Hi Ales!', $translator->translate(new Contributte\Translation\Wrappers\Message('hi', ['name' => 'Ales'])));
		Tester\Assert::same('Hi Ales!', $translator->translate('//messages.hi', ['name' => 'Ales']));
		Tester\Assert::same('Hi Ales!', $translator->translate(new Contributte\Translation\Wrappers\Message('//messages.hi', ['name' => 'Ales'])));
		Tester\Assert::same('Hi Ales!', $translator->translate('hi', ['name' => 'Ales'], 'messages', 'en'));
		Tester\Assert::same('Hi Ales!', $translator->translate(new Contributte\Translation\Wrappers\Message('hi', ['name' => 'Ales'], 'messages', 'en')));
		Tester\Assert::same('Hi Ales!', $translator->translate('hi', null, ['name' => 'Ales'], 'messages', 'en'));
		Tester\Assert::same('Hi Ales!', $translator->translate(new Contributte\Translation\Wrappers\Message('hi', null, ['name' => 'Ales'], 'messages', 'en')));
		Tester\Assert::same('There are no apples', $translator->translate('messages.apples', 0));
		Tester\Assert::same('There are no apples', $translator->translate('messages.apples', ['count' => 0]));
		Tester\Assert::same('There are no apples', $translator->translate('messages.apples', 0.0));
		Tester\Assert::same('There are no apples', $translator->translate('messages.apples', ['count' => 0.0]));
		Tester\Assert::same('There is one apple', $translator->translate('messages.apples', 1));
		Tester\Assert::same('There is one apple', $translator->translate('messages.apples', ['count' => 1]));
		Tester\Assert::same('There is one apple', $translator->translate('messages.apples', 1.0));
		Tester\Assert::same('There is one apple', $translator->translate('messages.apples', ['count' => 1.0]));
		Tester\Assert::same('There are 1.9 apples', $translator->translate('messages.apples', 1.9));
		Tester\Assert::same('There are 1.9 apples', $translator->translate('messages.apples', ['count' => 1.9]));
		Tester\Assert::same('There are 2 apples', $translator->translate('messages.apples', 2));
		Tester\Assert::same('There are 2 apples', $translator->translate('messages.apples', ['count' => 2]));
		Tester\Assert::same('There are 2 apples', $translator->translate('messages.apples', 2.0));
		Tester\Assert::same('There are 2 apples', $translator->translate('messages.apples', ['count' => 2.0]));
		Tester\Assert::same('There are 2.9 apples', $translator->translate('messages.apples', 2.9));
		Tester\Assert::same('There are 2.9 apples', $translator->translate('messages.apples', ['count' => 2.9]));
		Tester\Assert::same('There are 5.5 apples', $translator->translate('messages.apples', 5.5));
		Tester\Assert::same('There are 5.5 apples', $translator->translate('messages.apples', ['count' => 5.5]));
		Tester\Assert::same('There are 5.5 apples', $translator->translate('messages.apples', 5.5));
		Tester\Assert::same('There are 5.5 apples', $translator->translate('messages.apples', ['count' => 5.5]));
		Tester\Assert::same('There are 5.9 apples', $translator->translate('messages.apples', 5.9));
		Tester\Assert::same('There are 5.9 apples', $translator->translate('messages.apples', ['count' => 5.9]));
		Tester\Assert::same('Depth message', $translator->translate('messages.depth.message'));
		Tester\Assert::same('missing.translation', $translator->translate('messages.missing.translation'));
		Tester\Assert::same('Empty string key', $translator->translate('messages.'));
		Tester\Assert::same('emptyDomain', $translator->translate('.emptyDomain'));
		Tester\Assert::same('messages.some broken message', $translator->translate('messages.some broken message'));

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


	public function test03(): void
	{
		$container = $this->createContainer();

		/** @var Nette\Application\UI\ITemplateFactory $translator */
		$template = $container->getByType(Nette\Application\UI\ITemplateFactory::class);

		$template->onCreate[] = function (Nette\Bridges\ApplicationLatte\Template $template): void {
			Tester\Assert::same('Hello', $template->renderToString(Tester\FileMock::create('{_messages.hello}')));
			Tester\Assert::same('Hello', $template->renderToString(Tester\FileMock::create('{_}messages.hello{/_}')));
			Tester\Assert::same('Hello', $template->renderToString(Tester\FileMock::create('{php $message = "messages.hello"}{$message|translate}')));

			Tester\Assert::same('Hello', $template->renderToString(Tester\FileMock::create('{_hello}')));
			Tester\Assert::same('Hello', $template->renderToString(Tester\FileMock::create('{_}hello{/_}')));
			Tester\Assert::same('Hello', $template->renderToString(Tester\FileMock::create('{php $message = "hello"}{$message|translate}')));

			Tester\Assert::same('Hello', $template->renderToString(Tester\FileMock::create('{_//messages.hello}')));
			Tester\Assert::same('Hello', $template->renderToString(Tester\FileMock::create('{_}//messages.hello{/_}')));
			Tester\Assert::same('Hello', $template->renderToString(Tester\FileMock::create('{php $message = "//messages.hello"}{$message|translate}')));

			Tester\Assert::same('Hello', $template->renderToString(Tester\FileMock::create('{_hello, [], messages, en}')));
			Tester\Assert::same('Hello', $template->renderToString(Tester\FileMock::create('{php $message = "hello"}{$message|translate: [], messages, en}')));

			Tester\Assert::same('Hello', $template->renderToString(Tester\FileMock::create('{_hello, null, [], messages, en}')));
			Tester\Assert::same('Hello', $template->renderToString(Tester\FileMock::create('{php $message = "hello"}{$message|translate: null, [], messages, en}')));

			Tester\Assert::same('Hi Ales!', $template->renderToString(Tester\FileMock::create('{_messages.hi, [name => Ales]}')));
			Tester\Assert::same('Hi Ales!', $template->renderToString(Tester\FileMock::create('{php $message = "messages.hi"}{$message|translate: [name => Ales]}')));

			Tester\Assert::same('Hi Ales!', $template->renderToString(Tester\FileMock::create('{_hi, [name => Ales]}')));
			Tester\Assert::same('Hi Ales!', $template->renderToString(Tester\FileMock::create('{php $message = "hi"}{$message|translate: [name => Ales]}')));

			Tester\Assert::same('Hi Ales!', $template->renderToString(Tester\FileMock::create('{_//messages.hi, [name => Ales]}')));
			Tester\Assert::same('Hi Ales!', $template->renderToString(Tester\FileMock::create('{php $message = "//messages.hi"}{$message|translate: [name => Ales]}')));

			Tester\Assert::same('Hi Ales!', $template->renderToString(Tester\FileMock::create('{_hi, [name => Ales], messages, en}')));
			Tester\Assert::same('Hi Ales!', $template->renderToString(Tester\FileMock::create('{php $message = "hi"}{$message|translate: [name => Ales], messages, en}')));

			Tester\Assert::same('Hi Ales!', $template->renderToString(Tester\FileMock::create('{_hi, null, [name => Ales], messages, en}')));
			Tester\Assert::same('Hi Ales!', $template->renderToString(Tester\FileMock::create('{php $message = "hi"}{$message|translate: null, [name => Ales], messages, en}')));

			Tester\Assert::same('Depth message', $template->renderToString(Tester\FileMock::create('{_messages.depth.message}')));
			Tester\Assert::same('Depth message', $template->renderToString(Tester\FileMock::create('{translator messages}{_depth.message}{/translator}')));
			Tester\Assert::same('Depth message', $template->renderToString(Tester\FileMock::create('{translator messages}{translator depth}{_message}{/translator}{/translator}')));

			Tester\Assert::same('missing.translation', $template->renderToString(Tester\FileMock::create('{_messages.missing.translation}')));
			Tester\Assert::same('missing.translation', $template->renderToString(Tester\FileMock::create('{php $message = "messages.missing.translation"}{$message|translate}')));
		};

		$template->createTemplate();
	}


	/**
	 * @internal
	 *
	 * @return Nette\DI\Container
	 */
	private function createContainer(): Nette\DI\Container
	{
		$configurator = new Nette\Configurator;

		$configurator->setTempDirectory($this->container->getParameters()['tempDir'])
			->addConfig([
				'extensions' => [
					'translation' => Contributte\Translation\DI\TranslationExtension::class,
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

		return $configurator->createContainer();
	}
}


(new Translator($container))->run();
