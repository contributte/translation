<?php declare(strict_types = 1);

namespace Tests;

use Contributte;
use Latte;
use Mockery;
use Nette;
use Psr;
use stdClass;
use Tester;
use Tests;

$container = require __DIR__ . '/../bootstrap.php';

class Translator extends Tests\TestAbstract
{

	public function test01(): void
	{
		$translator = new Contributte\Translation\Translator(new Contributte\Translation\LocaleResolver(Mockery::mock(Nette\DI\Container::class)), new Contributte\Translation\FallbackResolver(), 'en', __DIR__ . '/cacheDir', true);

		Tester\Assert::true($translator->localeResolver instanceof Contributte\Translation\LocaleResolver);
		Tester\Assert::true($translator->fallbackResolver instanceof Contributte\Translation\FallbackResolver);
		Tester\Assert::same('en', $translator->defaultLocale);
		Tester\Assert::same(__DIR__ . '/cacheDir', $translator->cacheDir);
		Tester\Assert::true($translator->debug);
		Tester\Assert::null($translator->localesWhitelist);
		Tester\Assert::same([], $translator->prefix);
		Tester\Assert::same('', $translator->formattedPrefix);
		Tester\Assert::same([], $translator->availableLocales);
		Tester\Assert::same('en', $translator->locale);

		$translator->setLocalesWhitelist(['en', 'cs']);

		Tester\Assert::same(['en', 'cs'], $translator->localesWhitelist);

		$translator->setPrefix(['prefix']);

		Tester\Assert::same('prefix', $translator->formattedPrefix);

		$translator->addPrefix('next');

		Tester\Assert::same('prefix.next', $translator->formattedPrefix);

		$translator->removePrefix();

		Tester\Assert::same('prefix', $translator->formattedPrefix);

		$translator->setPrefix([]);

		$translator->addPrefix('prefix');

		Tester\Assert::same('prefix', $translator->formattedPrefix);

		$translator->removePrefix('prefix');

		Tester\Assert::exception(function () use ($translator): void {
			$translator->removePrefix();
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'Can not remove empty prefix.');
		Tester\Assert::exception(function () use ($translator): void {
			$translator->removePrefix('unknown');
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'Unknown "unknown" prefix.');

		$translator->addResource('neon', __DIR__ . '/file.neon', 'en_US', 'domain');
		$translator->addResource('neon', __DIR__ . '/file.neon', 'cs_CZ', 'domain');

		Tester\Assert::same(['cs_CZ', 'en_US'], $translator->availableLocales);

		Tester\Assert::null($translator->psrLogger);
		$translator->setPsrLogger(new PsrLoggerMock());
		Tester\Assert::true($translator->psrLogger instanceof PsrLoggerMock);

		Tester\Assert::null($translator->tracyPanel);
		new Contributte\Translation\Tracy\Panel($translator);
		Tester\Assert::true($translator->tracyPanel instanceof Contributte\Translation\Tracy\Panel);
	}

	public function test02(): void
	{
		$container = Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir']);

		/** @var Contributte\Translation\Translator $translator */
		$translator = $container->getByType(Nette\Localization\ITranslator::class);

		Tester\Assert::throws(function () use ($translator): void {
			$translator->translate(new stdClass());
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'Message must be string, object given.');
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
		Tester\Assert::same('Overloaded message', $translator->translate('messages.overloading.message'));
		Tester\Assert::same('missing.translation', $translator->translate('messages.missing.translation'));
		Tester\Assert::same('', $translator->translate('messages.'));
		Tester\Assert::same('emptyDomain', $translator->translate('.emptyDomain'));
		Tester\Assert::same('some broken message', $translator->translate('messages.some broken message'));
		Tester\Assert::same('Yes, we can!', $translator->translate('another_domain.Can you translate this message?'));

		$translator->addPrefix('messages');

		Tester\Assert::same('Hello', $translator->translate('hello'));
		Tester\Assert::same('Hello', $translator->translate('//messages.hello'));
		Tester\Assert::same('messages.hello', $translator->translate('messages.hello'));
		Tester\Assert::same('Hi Ales!', $translator->translate('hi', ['name' => 'Ales']));
		Tester\Assert::same('Hi Ales!', $translator->translate('//messages.hi', ['name' => 'Ales']));
		Tester\Assert::same('messages.hi', $translator->translate('messages.hi', ['name' => 'Ales']));

		$translator->removePrefix();

		$translator->addPrefix('another_domain');

		Tester\Assert::same('Yes, we can!', $translator->translate('Can you translate this message?'));

		$translator->removePrefix();

		$prefixedTranslator = $translator->createPrefixedTranslator('messages');
		Tester\Assert::same('Hello', $prefixedTranslator->translate('hello'));
	}

	public function test03(): void
	{
		$container = Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir']);

		/** @var Latte\Engine $latte */
		$latte = $container->getByType(Nette\Bridges\ApplicationLatte\ILatteFactory::class)->create();

		Tester\Assert::same('Hello', $latte->renderToString(Tester\FileMock::create('{_messages.hello}')));
		Tester\Assert::same('Hello', $latte->renderToString(Tester\FileMock::create('{_}messages.hello{/_}')));
		Tester\Assert::same('Hello', $latte->renderToString(Tester\FileMock::create('{_}{php $message = "messages.hello"}{$message}{/_}')));
		Tester\Assert::same('Hello', $latte->renderToString(Tester\FileMock::create('{php $message = "messages.hello"}{$message|translate}')));

		Tester\Assert::same('Hello', $latte->renderToString(Tester\FileMock::create('{_hello}')));
		Tester\Assert::same('Hello', $latte->renderToString(Tester\FileMock::create('{_}hello{/_}')));
		Tester\Assert::same('Hello', $latte->renderToString(Tester\FileMock::create('{_}{php $message = "hello"}{$message}{/_}')));
		Tester\Assert::same('Hello', $latte->renderToString(Tester\FileMock::create('{php $message = "hello"}{$message|translate}')));

		Tester\Assert::same('Hello', $latte->renderToString(Tester\FileMock::create('{_//messages.hello}')));
		Tester\Assert::same('Hello', $latte->renderToString(Tester\FileMock::create('{_}//messages.hello{/_}')));
		Tester\Assert::same('Hello', $latte->renderToString(Tester\FileMock::create('{_}{php $message = "//messages.hello"}{$message}{/_}')));
		Tester\Assert::same('Hello', $latte->renderToString(Tester\FileMock::create('{php $message = "//messages.hello"}{$message|translate}')));

		Tester\Assert::same('Hello', $latte->renderToString(Tester\FileMock::create('{_hello, [], messages, en}')));
		Tester\Assert::same('Hello', $latte->renderToString(Tester\FileMock::create('{php $message = "hello"}{$message|translate: [], messages, en}')));

		Tester\Assert::same('Hello', $latte->renderToString(Tester\FileMock::create('{_hello, null, [], messages, en}')));
		Tester\Assert::same('Hello', $latte->renderToString(Tester\FileMock::create('{php $message = "hello"}{$message|translate: null, [], messages, en}')));

		Tester\Assert::same('Hi Ales!', $latte->renderToString(Tester\FileMock::create('{_messages.hi, [name => Ales]}')));
		Tester\Assert::same('Hi Ales!', $latte->renderToString(Tester\FileMock::create('{php $message = "messages.hi"}{$message|translate: [name => Ales]}')));

		Tester\Assert::same('Hi Ales!', $latte->renderToString(Tester\FileMock::create('{_hi, [name => Ales]}')));
		Tester\Assert::same('Hi Ales!', $latte->renderToString(Tester\FileMock::create('{php $message = "hi"}{$message|translate: [name => Ales]}')));

		Tester\Assert::same('Hi Ales!', $latte->renderToString(Tester\FileMock::create('{_//messages.hi, [name => Ales]}')));
		Tester\Assert::same('Hi Ales!', $latte->renderToString(Tester\FileMock::create('{php $message = "//messages.hi"}{$message|translate: [name => Ales]}')));

		Tester\Assert::same('Hi Ales!', $latte->renderToString(Tester\FileMock::create('{_hi, [name => Ales], messages, en}')));
		Tester\Assert::same('Hi Ales!', $latte->renderToString(Tester\FileMock::create('{php $message = "hi"}{$message|translate: [name => Ales], messages, en}')));

		Tester\Assert::same('Hi Ales!', $latte->renderToString(Tester\FileMock::create('{_hi, null, [name => Ales], messages, en}')));
		Tester\Assert::same('Hi Ales!', $latte->renderToString(Tester\FileMock::create('{php $message = "hi"}{$message|translate: null, [name => Ales], messages, en}')));

		Tester\Assert::same('Depth message', $latte->renderToString(Tester\FileMock::create('{_messages.depth.message}')));

		Tester\Assert::same('missing.translation', $latte->renderToString(Tester\FileMock::create('{_messages.missing.translation}')));
		Tester\Assert::same('missing.translation', $latte->renderToString(Tester\FileMock::create('{php $message = "messages.missing.translation"}{$message|translate}')));

		Tester\Assert::same('Depth message', $latte->renderToString(Tester\FileMock::create('{translator messages}{_depth.message}{/translator}')));
		Tester\Assert::same('Very very depth message', $latte->renderToString(Tester\FileMock::create('{translator messages}{translator messages.very.very.depth}{_message}{/translator}{/translator}')));
		Tester\Assert::same('Depth message-Very very depth message-Depth message', $latte->renderToString(Tester\FileMock::create('{translator messages}{_depth.message}{translator messages.very.very.depth}-{_message}-{/translator}{_depth.message}{/translator}')));
		Tester\Assert::exception(function () use ($latte): void {
			$latte->renderToString(Tester\FileMock::create('{translator}{_depth.message}{/translator}'));
		}, Latte\CompileException::class);
	}

	public function test04(): void
	{
		$container = Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
			'translation' => [
				'locales' => [
					'whitelist' => ['en'],
				],
			],
		]);

		/** @var Contributte\Translation\Tracy\Panel $panel */
		$panel = $container->getByType(Contributte\Translation\Tracy\Panel::class);

		/** @var Contributte\Translation\Translator $translator */
		$translator = $container->getByType(Nette\Localization\ITranslator::class);

		$translator->translate('untranslated');// add missing translation
		$dom = Tester\DomQuery::fromHtml($panel->getPanel());

		Tester\Assert::same('en', (string) $dom->find('td[class="contributte-translation-default-locale"]')[0]);
		Tester\Assert::same('en', (string) $dom->find('td[class="contributte-translation-locales-whitelist"]')[0]);
		Tester\Assert::count(1, $dom->find('tr[class="contributte-translation-missing-translation"]'));
		Tester\Assert::count(1, $dom->find('tr[class="contributte-translation-locale-resolvers"]'));
		Tester\Assert::count(3, $dom->find('tr[class="contributte-translation-resources"]'));// lang/another_domain.en_US.neon, lang/messages.en_US.neon, lang_overloading/messages.en_US.neon
		Tester\Assert::count(1, $dom->find('tr[class="contributte-translation-ignored-resources"]'));// lang/messages.cs_CZ.neon

		$psrLogger = new class() extends Psr\Log\AbstractLogger {

			/**
			 * @inheritDoc
			 */
			public function log($level, $message, array $context = [])
			{
				Tester\Assert::same(Psr\Log\LogLevel::NOTICE, $level);
				Tester\Assert::same('Missing translation', $message);
				Tester\Assert::same(['id' => 'untranslated', 'domain' => 'somedomain', 'locale' => 'en'], $context);
			}

		};

		$translator->setPsrLogger($psrLogger);
		$translator->translate('somedomain.untranslated');
	}

}

(new Translator($container))->run();
