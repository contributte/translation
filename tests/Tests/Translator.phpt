<?php declare(strict_types = 1);

namespace Tests;

use Contributte;
use Latte;
use Mockery;
use Nette;
use Psr;
use stdClass;
use Tester\Assert;
use Tester\DomQuery;
use Tester\FileMock;
use Tests;

$container = require __DIR__ . '/../bootstrap.php';

class Translator extends Tests\TestAbstract
{

	public function test01(): void
	{
		$translator = new Contributte\Translation\Translator(new Contributte\Translation\LocaleResolver(Mockery::mock(Nette\DI\Container::class)), new Contributte\Translation\FallbackResolver(), 'en', __DIR__ . '/cacheDir', true);

		Assert::true($translator->getLocaleResolver() instanceof Contributte\Translation\LocaleResolver);
		Assert::true($translator->getFallbackResolver() instanceof Contributte\Translation\FallbackResolver);
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

		Assert::exception(function () use ($translator): void {
			$translator->removePrefix();
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'Can not remove empty prefix.');
		Assert::exception(function () use ($translator): void {
			$translator->removePrefix('unknown');
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'Unknown "unknown" prefix.');

		$translator->addResource('neon', __DIR__ . '/file.neon', 'en_US', 'domain');
		$translator->addResource('neon', __DIR__ . '/file.neon', 'cs_CZ', 'domain');

		Assert::same(['cs_CZ', 'en_US'], $translator->getAvailableLocales());

		Assert::null($translator->getPsrLogger());
		$translator->setPsrLogger(new PsrLoggerMock());
		Assert::true($translator->getPsrLogger() instanceof PsrLoggerMock);

		Assert::null($translator->getTracyPanel());
		new Contributte\Translation\Tracy\Panel($translator);
		Assert::true($translator->getTracyPanel() instanceof Contributte\Translation\Tracy\Panel);
	}

	public function test02(): void
	{
		$container = Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir']);

		/** @var Contributte\Translation\Translator $translator */
		$translator = $container->getByType(Nette\Localization\ITranslator::class);

		Assert::throws(function () use ($translator): void {
			$translator->translate(new stdClass());
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'Message must be string, object given.');
		Assert::same('', $translator->translate(null));
		Assert::same('', $translator->translate(''));
		Assert::same('0', $translator->translate(0));
		Assert::same('1', $translator->translate(1));
		Assert::same('Not translate!', $translator->translate(new Contributte\Translation\Wrappers\NotTranslate('Not translate!')));
		Assert::same('Hello', $translator->translate('messages.hello'));
		Assert::same('Hello', $translator->translate(new Contributte\Translation\Wrappers\Message('messages.hello')));
		Assert::same('Hello', $translator->translate('hello'));
		Assert::same('Hello', $translator->translate(new Contributte\Translation\Wrappers\Message('hello')));
		Assert::same('Hello', $translator->translate('//messages.hello'));
		Assert::same('Hello', $translator->translate(new Contributte\Translation\Wrappers\Message('//messages.hello')));
		Assert::same('Hello', $translator->translate('hello', [], 'messages', 'en'));
		Assert::same('Hello', $translator->translate(new Contributte\Translation\Wrappers\Message('hello', [], 'messages', 'en')));
		Assert::same('Hello', $translator->translate('hello', null, [], 'messages', 'en'));
		Assert::same('Hello', $translator->translate(new Contributte\Translation\Wrappers\Message('hello', null, [], 'messages', 'en')));
		Assert::same('Hi Ales!', $translator->translate('messages.hi', ['name' => 'Ales']));
		Assert::same('Hi Ales!', $translator->translate(new Contributte\Translation\Wrappers\Message('messages.hi', ['name' => 'Ales'])));
		Assert::same('Hi Ales!', $translator->translate('hi', ['name' => 'Ales']));
		Assert::same('Hi Ales!', $translator->translate(new Contributte\Translation\Wrappers\Message('hi', ['name' => 'Ales'])));
		Assert::same('Hi Ales!', $translator->translate('//messages.hi', ['name' => 'Ales']));
		Assert::same('Hi Ales!', $translator->translate(new Contributte\Translation\Wrappers\Message('//messages.hi', ['name' => 'Ales'])));
		Assert::same('Hi Ales!', $translator->translate('hi', ['name' => 'Ales'], 'messages', 'en'));
		Assert::same('Hi Ales!', $translator->translate(new Contributte\Translation\Wrappers\Message('hi', ['name' => 'Ales'], 'messages', 'en')));
		Assert::same('Hi Ales!', $translator->translate('hi', null, ['name' => 'Ales'], 'messages', 'en'));
		Assert::same('Hi Ales!', $translator->translate(new Contributte\Translation\Wrappers\Message('hi', null, ['name' => 'Ales'], 'messages', 'en')));
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
	}

	public function test03(): void
	{
		$container = Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir']);

		/** @var Latte\Engine $latte */
		$latte = $container->getByType(Nette\Bridges\ApplicationLatte\ILatteFactory::class)->create();

		/** @var Contributte\Translation\Translator $translator */
		$translator = $container->getByType(Nette\Localization\ITranslator::class);

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
		Assert::exception(function () use ($latte): void {
			$latte->renderToString(FileMock::create('{translator}{_depth.message}{/translator}'));
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
		$dom = DomQuery::fromHtml($panel->getPanel());

		Assert::same('en', (string) $dom->find('td[class="contributte-translation-default-locale"]')[0]);
		Assert::same('en', (string) $dom->find('td[class="contributte-translation-locales-whitelist"]')[0]);
		Assert::count(1, $dom->find('tr[class="contributte-translation-missing-translation"]'));
		Assert::count(1, $dom->find('tr[class="contributte-translation-locale-resolvers"]'));
		Assert::count(3, $dom->find('tr[class="contributte-translation-resources"]'));// lang/another_domain.en_US.neon, lang/messages.en_US.neon, lang_overloading/messages.en_US.neon
		Assert::count(1, $dom->find('tr[class="contributte-translation-ignored-resources"]'));// lang/messages.cs_CZ.neon

		$psrLogger = new class() extends Psr\Log\AbstractLogger {

			/**
			 * @inheritDoc
			 */
			public function log($level, $message, array $context = [])
			{
				Assert::same(Psr\Log\LogLevel::NOTICE, $level);
				Assert::same('Missing translation', $message);
				Assert::same(['id' => 'untranslated', 'domain' => 'somedomain', 'locale' => 'en'], $context);
			}

		};

		$translator->setPsrLogger($psrLogger);
		$translator->translate('somedomain.untranslated');
	}

}

(new Translator($container))->run();
