<?php declare(strict_types = 1);

namespace Tests;

use Contributte\Translation\Exceptions\InvalidArgument;
use Contributte\Translation\FallbackResolver;
use Contributte\Translation\LocaleResolver;
use Contributte\Translation\Tracy\Panel;
use Contributte\Translation\Translator;
use Contributte\Translation\Wrappers\Message;
use Contributte\Translation\Wrappers\NotTranslate;
use Latte\CompileException;
use Mockery;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\DI\Container;
use Nette\Localization\ITranslator;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use stdClass;
use Tester\Assert;
use Tester\DomQuery;
use Tester\FileMock;

$container = require __DIR__ . '/../bootstrap.php';

final class TranslatorTest extends TestAbstract
{

	public function test01(): void
	{
		$translator = new Translator(new LocaleResolver(Mockery::mock(Container::class)), new FallbackResolver(), 'en', __DIR__ . '/cacheDir', true);

		Assert::true($translator->getLocaleResolver() instanceof LocaleResolver);
		Assert::true($translator->getFallbackResolver() instanceof FallbackResolver);
		Assert::same('en', $translator->getDefaultLocale());
		Assert::same(__DIR__ . '/cacheDir', $translator->getCacheDir());
		Assert::true($translator->getDebug());
		Assert::null($translator->getLocalesWhitelist());
		Assert::same([], $translator->getPrefix());
		Assert::same('', $translator->getFormattedPrefix());
		Assert::same([], $translator->getAvailableLocales());
		Assert::same('en', $translator->getLocale());
		Assert::true($translator->returnOriginalMessage);

		$translator->setLocalesWhitelist(['en', 'cs']);

		Assert::same(['en', 'cs'], $translator->getLocalesWhitelist());

		$translator->setPrefix(['prefix']);

		Assert::same('prefix', $translator->getFormattedPrefix());

		$translator->addPrefix('next');

		Assert::same('prefix.next', $translator->getFormattedPrefix());

		$translator->removePrefix();

		Assert::same('prefix', $translator->getFormattedPrefix());

		$translator->setPrefix([]);

		$translator->addPrefix('prefix');

		Assert::same('prefix', $translator->getFormattedPrefix());

		$translator->removePrefix('prefix');

		Assert::exception(static function () use ($translator): void {
			$translator->removePrefix();
		}, InvalidArgument::class, 'Can not remove empty prefix.');
		Assert::exception(static function () use ($translator): void {
			$translator->removePrefix('unknown');
		}, InvalidArgument::class, 'Unknown "unknown" prefix.');

		$translator->addResource('neon', __DIR__ . '/file.neon', 'en_US', 'domain');
		$translator->addResource('neon', __DIR__ . '/file.neon', 'cs_CZ', 'domain');

		Assert::same(['cs_CZ', 'en_US'], $translator->getAvailableLocales());

		Assert::null($translator->getPsrLogger());
		$translator->setPsrLogger(new PsrLoggerMock());
		Assert::true($translator->getPsrLogger() instanceof PsrLoggerMock);

		Assert::null($translator->getTracyPanel());
		new Panel($translator);
		Assert::true($translator->getTracyPanel() instanceof Panel);
	}

	public function test02(): void
	{
		$container = Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir']);

		/** @var \Contributte\Translation\Translator $translator */
		$translator = $container->getByType(ITranslator::class);

		Assert::throws(static function () use ($translator): void {
			$translator->translate(new stdClass());
		}, InvalidArgument::class, 'Message must be string, object given.');
		Assert::same('', $translator->translate(null));
		Assert::same('', $translator->translate(''));
		Assert::same('0', $translator->translate(0));
		Assert::same('1', $translator->translate(1));
		Assert::same('Not translate!', $translator->translate(new NotTranslate('Not translate!')));
		Assert::same('Hello', $translator->translate('messages.hello'));
		Assert::same('Hello', $translator->translate(new Message('messages.hello')));
		Assert::same('Hello', $translator->translate('hello'));
		Assert::same('Hello', $translator->translate(new Message('hello')));
		Assert::same('Hello', $translator->translate('//messages.hello'));
		Assert::same('Hello', $translator->translate(new Message('//messages.hello')));
		Assert::same('Hello', $translator->translate('hello', [], 'messages', 'en'));
		Assert::same('Hello', $translator->translate(new Message('hello', [], 'messages', 'en')));
		Assert::same('Hello', $translator->translate('hello', null, [], 'messages', 'en'));
		Assert::same('Hello', $translator->translate(new Message('hello', null, [], 'messages', 'en')));
		Assert::same('Hi Ales!', $translator->translate('messages.hi', ['name' => 'Ales']));
		Assert::same('Hi Ales!', $translator->translate(new Message('messages.hi', ['name' => 'Ales'])));
		Assert::same('Hi Ales!', $translator->translate('hi', ['name' => 'Ales']));
		Assert::same('Hi Ales!', $translator->translate(new Message('hi', ['name' => 'Ales'])));
		Assert::same('Hi Ales!', $translator->translate('//messages.hi', ['name' => 'Ales']));
		Assert::same('Hi Ales!', $translator->translate(new Message('//messages.hi', ['name' => 'Ales'])));
		Assert::same('Hi Ales!', $translator->translate('hi', ['name' => 'Ales'], 'messages', 'en'));
		Assert::same('Hi Ales!', $translator->translate(new Message('hi', ['name' => 'Ales'], 'messages', 'en')));
		Assert::same('Hi Ales!', $translator->translate('hi', null, ['name' => 'Ales'], 'messages', 'en'));
		Assert::same('Hi Ales!', $translator->translate(new Message('hi', null, ['name' => 'Ales'], 'messages', 'en')));
		Assert::same('There are no apples', $translator->translate('messages.apples', 0));
		Assert::same('There are no apples', $translator->translate('messages.apples', ['count' => 0]));
		Assert::same('There are no apples', $translator->translate('messages.apples', 0.0));
		Assert::same('There are no apples', $translator->translate('messages.apples', ['count' => 0.0]));
		Assert::same('There is one apple', $translator->translate('messages.apples', 1));
		Assert::same('There is one apple', $translator->translate('messages.apples', ['count' => 1]));
		Assert::same('There is one apple', $translator->translate('messages.apples', 1.0));
		Assert::same('There is one apple', $translator->translate('messages.apples', ['count' => 1.0]));
		Assert::same('There are 1.9 apples', $translator->translate('messages.apples', 1.9));
		Assert::same('There are 1.9 apples', $translator->translate('messages.apples', ['count' => 1.9]));
		Assert::same('There are 2 apples', $translator->translate('messages.apples', 2));
		Assert::same('There are 2 apples', $translator->translate('messages.apples', ['count' => 2]));
		Assert::same('There are 2 apples', $translator->translate('messages.apples', 2.0));
		Assert::same('There are 2 apples', $translator->translate('messages.apples', ['count' => 2.0]));
		Assert::same('There are 2.9 apples', $translator->translate('messages.apples', 2.9));
		Assert::same('There are 2.9 apples', $translator->translate('messages.apples', ['count' => 2.9]));
		Assert::same('There are 5.5 apples', $translator->translate('messages.apples', 5.5));
		Assert::same('There are 5.5 apples', $translator->translate('messages.apples', ['count' => 5.5]));
		Assert::same('There are 5.5 apples', $translator->translate('messages.apples', 5.5));
		Assert::same('There are 5.5 apples', $translator->translate('messages.apples', ['count' => 5.5]));
		Assert::same('There are 5.9 apples', $translator->translate('messages.apples', 5.9));
		Assert::same('There are 5.9 apples', $translator->translate('messages.apples', ['count' => 5.9]));
		Assert::same('Depth message', $translator->translate('messages.depth.message'));
		Assert::same('Overloaded message', $translator->translate('messages.overloading.message'));

		$translator->returnOriginalMessage = false;
		Assert::same('missing.translation', $translator->translate('messages.missing.translation'));

		$translator->returnOriginalMessage = true;
		Assert::same('messages.missing.translation', $translator->translate('messages.missing.translation'));
		Assert::same('for', $translator->translate('another_domain.for'));
		Assert::same('another_domain.foreach', $translator->translate('another_domain.foreach'));

		$translator->returnOriginalMessage = false;
		Assert::same('', $translator->translate('messages.'));

		$translator->returnOriginalMessage = true;
		Assert::same('messages.', $translator->translate('messages.'));

		$translator->returnOriginalMessage = false;
		Assert::same('emptyDomain', $translator->translate('.emptyDomain'));

		$translator->returnOriginalMessage = true;
		Assert::same('.emptyDomain', $translator->translate('.emptyDomain'));

		$translator->returnOriginalMessage = false;
		Assert::same('some broken message', $translator->translate('messages.some broken message'));

		$translator->returnOriginalMessage = true;
		Assert::same('messages.some broken message', $translator->translate('messages.some broken message'));

		Assert::same('Yes, we can!', $translator->translate('another_domain.Can you translate this message?'));

		$translator->addPrefix('messages');

		Assert::same('Hello', $translator->translate('hello'));
		Assert::same('Hello', $translator->translate('//messages.hello'));
		Assert::same('messages.hello', $translator->translate('messages.hello'));
		Assert::same('Hi Ales!', $translator->translate('hi', ['name' => 'Ales']));
		Assert::same('Hi Ales!', $translator->translate('//messages.hi', ['name' => 'Ales']));
		Assert::same('messages.hi', $translator->translate('messages.hi', ['name' => 'Ales']));

		$translator->removePrefix();
		$translator->addPrefix('another_domain');

		Assert::same('Yes, we can!', $translator->translate('Can you translate this message?'));

		$translator->removePrefix();

		$prefixedTranslator = $translator->createPrefixedTranslator('messages');
		Assert::same('Hello', $prefixedTranslator->translate('hello'));

		Assert::same('no_exists', $translator->translate('no_exists', [], 'messages'));

		Assert::same('Contact', $translator->translate('plural', 1));
		Assert::same('Contacts', $translator->translate('plural', 2));
		Assert::same('Contacts', $translator->translate('plural', 3));
		Assert::same('Contacts', $translator->translate('plural', 4));
		Assert::same('Contacts', $translator->translate('plural', 5));

		$translator->setLocale('cs');

		Assert::same('Kontakt', $translator->translate('plural', 1));
		Assert::same('Kontakty', $translator->translate('plural', 2));
		Assert::same('Kontakty', $translator->translate('plural', 3));
		Assert::same('Kontakty', $translator->translate('plural', 4));
		Assert::same('Hodně kontaktů', $translator->translate('plural', 5));

		Assert::same('It\'s a boy!', $translator->translate('baby_gender', ['gender' => 'boy']));
		Assert::same('It\'s a girl!', $translator->translate('baby_gender', ['gender' => 'girl']));
		Assert::same('It\'s something else!', $translator->translate('baby_gender', ['gender' => 'kibork']));

		$translator->setLocale('en_US');

		Assert::same('Přelož', $translator->translate('keyOnlyInCsCz', null, [], 'messages', 'cs_CZ'));
	}

	public function test03(): void
	{
		$container = Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir']);

		/** @var \Latte\Engine $latte */
		$latte = $container->getByType(ILatteFactory::class)->create();

		/** @var \Contributte\Translation\Translator $translator */
		$translator = $container->getByType(ITranslator::class);

		Assert::same('Hello', $latte->renderToString(FileMock::create('{_messages.hello}')));
		Assert::same('Hello', $latte->renderToString(FileMock::create('{_}messages.hello{/_}')));
		Assert::same('Hello', $latte->renderToString(FileMock::create('{_}{php $message = "messages.hello"}{$message}{/_}')));
		Assert::same('Hello', $latte->renderToString(FileMock::create('{php $message = "messages.hello"}{$message|translate}')));

		Assert::same('Hello', $latte->renderToString(FileMock::create('{_hello}')));
		Assert::same('Hello', $latte->renderToString(FileMock::create('{_}hello{/_}')));
		Assert::same('Hello', $latte->renderToString(FileMock::create('{_}{php $message = "hello"}{$message}{/_}')));
		Assert::same('Hello', $latte->renderToString(FileMock::create('{php $message = "hello"}{$message|translate}')));

		Assert::same('Hello', $latte->renderToString(FileMock::create('{_//messages.hello}')));
		Assert::same('Hello', $latte->renderToString(FileMock::create('{_}//messages.hello{/_}')));
		Assert::same('Hello', $latte->renderToString(FileMock::create('{_}{php $message = "//messages.hello"}{$message}{/_}')));
		Assert::same('Hello', $latte->renderToString(FileMock::create('{php $message = "//messages.hello"}{$message|translate}')));

		Assert::same('Hello', $latte->renderToString(FileMock::create('{_hello, [], messages, en}')));
		Assert::same('Hello', $latte->renderToString(FileMock::create('{php $message = "hello"}{$message|translate: [], messages, en}')));

		Assert::same('Hello', $latte->renderToString(FileMock::create('{_hello, null, [], messages, en}')));
		Assert::same('Hello', $latte->renderToString(FileMock::create('{php $message = "hello"}{$message|translate: null, [], messages, en}')));

		Assert::same('Hi Ales!', $latte->renderToString(FileMock::create('{_messages.hi, [name => Ales]}')));
		Assert::same('Hi Ales!', $latte->renderToString(FileMock::create('{php $message = "messages.hi"}{$message|translate: [name => Ales]}')));

		Assert::same('Hi Ales!', $latte->renderToString(FileMock::create('{_hi, [name => Ales]}')));
		Assert::same('Hi Ales!', $latte->renderToString(FileMock::create('{php $message = "hi"}{$message|translate: [name => Ales]}')));

		Assert::same('Hi Ales!', $latte->renderToString(FileMock::create('{_//messages.hi, [name => Ales]}')));
		Assert::same('Hi Ales!', $latte->renderToString(FileMock::create('{php $message = "//messages.hi"}{$message|translate: [name => Ales]}')));

		Assert::same('Hi Ales!', $latte->renderToString(FileMock::create('{_hi, [name => Ales], messages, en}')));
		Assert::same('Hi Ales!', $latte->renderToString(FileMock::create('{php $message = "hi"}{$message|translate: [name => Ales], messages, en}')));

		Assert::same('Hi Ales!', $latte->renderToString(FileMock::create('{_hi, null, [name => Ales], messages, en}')));
		Assert::same('Hi Ales!', $latte->renderToString(FileMock::create('{php $message = "hi"}{$message|translate: null, [name => Ales], messages, en}')));

		Assert::same('Depth message', $latte->renderToString(FileMock::create('{_messages.depth.message}')));

		$translator->returnOriginalMessage = false;
		Assert::same('missing.translation', $latte->renderToString(FileMock::create('{_messages.missing.translation}')));

		$translator->returnOriginalMessage = true;
		Assert::same('messages.missing.translation', $latte->renderToString(FileMock::create('{_messages.missing.translation}')));

		$translator->returnOriginalMessage = false;
		Assert::same('missing.translation', $latte->renderToString(FileMock::create('{php $message = "messages.missing.translation"}{$message|translate}')));

		$translator->returnOriginalMessage = true;
		Assert::same('messages.missing.translation', $latte->renderToString(FileMock::create('{php $message = "messages.missing.translation"}{$message|translate}')));

		Assert::same('Depth message', $latte->renderToString(FileMock::create('{translator messages}{_depth.message}{/translator}')));
		Assert::same('Very very depth message', $latte->renderToString(FileMock::create('{translator messages}{translator messages.very.very.depth}{_message}{/translator}{/translator}')));
		Assert::same('Depth message-Very very depth message-Depth message', $latte->renderToString(FileMock::create('{translator messages}{_depth.message}{translator messages.very.very.depth}-{_message}-{/translator}{_depth.message}{/translator}')));
		Assert::exception(static function () use ($latte): void {
			$latte->renderToString(FileMock::create('{translator}{_depth.message}{/translator}'));
		}, CompileException::class);
		Assert::same('<div>Hello</div>', $latte->renderToString(FileMock::create('<div n:translator="some.prefix">{_//messages.hello}</div>')));
		Assert::same('Do not translate!', $latte->renderToString(FileMock::create('{php $wrapper = new \Contributte\Translation\Wrappers\NotTranslate("Do not translate!")}{_$wrapper}')));
		Assert::same('Hi Ales!', $latte->renderToString(FileMock::create('{php $wrapper = new \Contributte\Translation\Wrappers\Message("hi", ["name" => "Ales"])}{_$wrapper}')));
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

		/** @var \Contributte\Translation\Tracy\Panel $panel */
		$panel = $container->getByType(Panel::class);

		/** @var \Contributte\Translation\Translator $translator */
		$translator = $container->getByType(ITranslator::class);

		$translator->translate('untranslated');// add missing translation
		$dom = DomQuery::fromHtml($panel->getPanel());

		Assert::same('en', (string) $dom->find('td[class="contributte-translation-default-locale"]')[0]);
		Assert::same('en', (string) $dom->find('td[class="contributte-translation-locales-whitelist"]')[0]);
		Assert::count(1, $dom->find('tr[class="contributte-translation-missing-translation"]'));
		Assert::count(1, $dom->find('tr[class="contributte-translation-locale-resolvers"]'));
		Assert::count(4, $dom->find('tr[class="contributte-translation-resources"]'));// lang/another_domain.en_US.neon, lang/messages+intl-icu.en_US.neon, lang/messages.en_US.neon, lang_overloading/messages.en_US.neon
		Assert::count(1, $dom->find('tr[class="contributte-translation-ignored-resources"]'));// lang/messages.cs_CZ.neon

		$psrLogger = new class() extends AbstractLogger {

			/**
			 * @inheritDoc
			 */
			public function log(
				$level,
				$message,
				array $context = []
			)
			{
				Assert::same(LogLevel::NOTICE, $level);
				Assert::same('Missing translation', $message);
				Assert::same(['id' => 'untranslated', 'domain' => 'somedomain', 'locale' => 'en'], $context);
			}

		};

		$translator->setPsrLogger($psrLogger);
		$translator->translate('somedomain.untranslated');
	}

}

(new TranslatorTest($container))->run();
