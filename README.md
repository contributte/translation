![](https://heatbadger.now.sh/github/readme/contributte/translation/)

<p align=center>
	<a href="https://github.com/contributte/translation/actions"><img src="https://badgen.net/github/checks/contributte/translation"></a>
	<a href="https://codecov.io/gh/contributte/translation"><img src="https://badgen.net/codecov/c/github/contributte/translation"></a>
	<a href="https://packagist.org/packages/contributte/translation"><img src="https://badgen.net/packagist/dm/contributte/translation"></a>
	<a href="https://packagist.org/packages/contributte/translation"><img src="https://badgen.net/packagist/v/contributte/translation"></a>
</p>
<p align=center>
	<a href="https://packagist.org/packages/contributte/translation"><img src="https://badgen.net/packagist/php/contributte/translation"></a>
	<a href="https://github.com/contributte/translation"><img src="https://badgen.net/github/license/contributte/translation"></a>
	<a href="https://bit.ly/ctteg"><img src="https://badgen.net/badge/support/gitter/cyan"></a>
	<a href="https://bit.ly/cttfo"><img src="https://badgen.net/badge/support/forum/yellow"></a>
	<a href="https://contributte.org/partners.html"><img src="https://badgen.net/badge/sponsor/donations/F96854"></a>
</p>

<p align=center>
Website 🚀 <a href="https://contributte.org">contributte.org</a> | Contact 👨🏻‍💻 <a href="https://f3l1x.io">f3l1x.io</a> | Twitter 🐦 <a href="https://twitter.com/contributte">@contributte</a>
</p>

Symfony Translation integration for Nette Framework with locale resolving, Latte helpers, file loaders, and optional database loaders.

## Versions

| State  | Version  | Branch           | PHP          | Symfony      |
|--------|----------|------------------|--------------|--------------|
| stable | `^2.0.0` | `master`         | `^8.0`       | `^6.0\|^7.0` |
| stable | `^1.0.0` | `php74_symfony5` | `^7.4\|^8.0` | `^5.0`       |

## Content

- [Installation](#installation)
- [Configuration](#configuration)
	- [Locale resolvers](#locale-resolvers)
- [Examples](#examples)
	- [Presenter](#presenter)
	- [Model](#model)
	- [Latte](#latte)
	- [Neon](#neon)
	- [Parameters in messages](#parameters-in-messages)
- [Loaders](#loaders)
	- [File loaders](#file-loaders)
	- [Database loaders](#database-loaders)
		- [Doctrine](#doctrine)
		- [Nette Database](#nette-database)
- [Features](#features)
	- [Wrappers](#wrappers)
	- [TranslationProviderInterface](#translationproviderinterface)
- [Development](#development)

## Installation

To install latest version of `contributte/translation` use [Composer](https://getcomposer.org).

```bash
composer require contributte/translation
```

Register extension:

```neon
extensions:
	translation: Contributte\Translation\DI\TranslationExtension
```

## Configuration

Basic configuration:

```neon
translation:
	locales:
		whitelist: [en, cs, sk]
		default: en
		fallback: [en]
	dirs:
		- %appDir%/lang
	returnOriginalMessage: true # to not translate undefined messages, default is true
```
Note: The `fallback` configuration values should always reflect the **locale code** used in the file you wish to _fallback_ to (e.g. `en_US` for `messages.en_US.neon`, or `en` for `messages.en.neon`).

### Locale resolvers

This configuration instructs the extension how to resolve the locale and the order in which it will do so:

```neon
translation:
	localeResolvers:
		- Contributte\Translation\LocalesResolvers\Router
```

Available resolvers:

- Contributte\Translation\LocalesResolvers\Router
- Contributte\Translation\LocalesResolvers\Header (HTTP header)
- Contributte\Translation\LocalesResolvers\Parameter (Get parameter)
- Contributte\Translation\LocalesResolvers\Session

By default the `Router`, `Parameter` and `Session` resolvers expect the name of the parameter/key to be `locale`.

## Examples

### Presenter

```php
<?php declare(strict_types = 1);

namespace App;

use Nette;
use Contributte;

class BasePresenter extends Nette\Application\UI\Presenter
{

	/** @var Nette\Localization\ITranslator @inject */
	public $translator;

	/** @var Contributte\Translation\LocalesResolvers\Session @inject */
	public $translatorSessionResolver;


	public function handleChangeLocale(string $locale): void
	{
		$this->translatorSessionResolver->setLocale($locale);
		$this->redirect('this');
	}


	public function renderDefault(): void
	{
		$this->translator->translate('domain.message');
		$prefixedTranslator = $this->translator->createPrefixedTranslator('domain');
		$prefixedTranslator->translate('message');
	}

}
```

### Model

```php
<?php declare(strict_types = 1);

namespace App\Model;

use Nette;

class Model
{

	/** @var Nette\Localization\ITranslator */
	private $translator;


	public function __construct(Nette\Localization\ITranslator $translator)
	{
		$this->translator = $translator;
	}

}
```

### Latte

How to use on frontend.

```latte
{_domain.message}
{_domain.message, $count}
{_domain.message, [name => "Ales"]}

{translator domain}
	{_message}
	{_message, $count}
	{_message, [name => "Ales"]}
{/translator}

{var $myMessage = 'domain.message'}
{$myMessage|translate}
```

### Neon

File name format:

```
        locale
          |
         /--\
messages.en_US.neon
\______/       \__/
   |            |
 domain     extension
```

File content format:

```neon
prefix:
	for: "message" # messages.prefix.for
```

### Parameters in messages

Sometimes it is convenient to include a dynamic parameter in the translation - as seen in the Latte examples above.

Neon:

```neon
user.name.taken: "Sorry, the username %name% is already taken, please try a different one."
```

Latte:

```latte
{_user.name.taken, [name => "Ales"]}
```

Presenter/Model:

```php
$this->translator->translate('user.name.taken', [name => 'Ales']);
```

**Note**: When passing parameters to the translator, the parameter names must not be enclosed in `%` characters. This is done by `Contributte/Translation` automatically.

## Loaders

By default the extension will look for `.neon` files.

### File loaders

```neon
array: Symfony\Component\Translation\Loader\ArrayLoader
csv: Symfony\Component\Translation\Loader\CsvFileLoader
dat: Symfony\Component\Translation\Loader\IcuDatFileLoader
res: Symfony\Component\Translation\Loader\IcuResFileLoader
ini: Symfony\Component\Translation\Loader\IniFileLoader
json: Symfony\Component\Translation\Loader\JsonFileLoader
mo: Symfony\Component\Translation\Loader\MoFileLoader
php: Symfony\Component\Translation\Loader\PhpFileLoader
po: Symfony\Component\Translation\Loader\PoFileLoader
ts: Symfony\Component\Translation\Loader\QtFileLoader
xlf: Symfony\Component\Translation\Loader\XliffFileLoader
yml: Symfony\Component\Translation\Loader\YamlFileLoader
```

### Database loaders

Package includes database loaders for **[Doctrine 2](https://www.doctrine-project.org/)** and **[Nette Database 3](https://doc.nette.org/cs/3.0/database)**.

#### Doctrine

You must create a file with specific format in scanned dirs such as **messages.en_US.doctrine**. All parameters are optional, but the file has to exist.

```neon
table: "My\Entity" # if you specify the entity key, "messages" from file name will be ignored
id: "id" # id column name, default is "id"
locale: "locale" # locale column name, default is "locale"
message: "message" # message column name, default is "message"
```

Add loader to translation configuration:

```neon
translation:
	loaders:
		doctrine: Contributte\Translation\Loaders\Doctrine
```

Entity example:

```php
<?php declare(strict_types = 1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="messages")
 */
class Messages
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", nullable=false)
	 * @ORM\GeneratedValue
	 */
	public $messageId;

	/**
	 * @ORM\Column(type="string", nullable=false)
	 */
	public $id;

	/**
	 * @ORM\Column(type="string", nullable=false)
	 */
	public $locale;

	/**
	 * @ORM\Column(type="string", nullable=false)
	 */
	public $message;

}
```

#### Nette Database

You must create a file with specific format in scanned dirs such as **messages.en_US.nettedatabase**. All parameters are optional, but the file has to exist.

```neon
table: "my_table" # if you specify table key, "messages" from file name will be ignored
id: "id" # id column name, default is "id"
locale: "locale" # locale column name, default is "locale"
message: "message" # message column name, default is "message"
```

Add loader to translation configuration:

```neon
translation:
	loaders:
		nettedatabase: Contributte\Translation\Loaders\NetteDatabase
```

DB table example:

```sql
CREATE TABLE `messages` (
    `id` varchar(191) NOT NULL,
    `locale` char(5) NOT NULL,
    `message` varchar(191) NOT NULL,
    UNIQUE KEY `id` (`id`),
    KEY `locale` (`locale`),
    KEY `message` (`message`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Features

### Wrappers

It is possible to pass pluralization to components without pre-translation and avoiding double translation.

```php
$form = new Nette\Application\UI\Form;

$form->addText('mail', 'form.mail.label')
	->setOption('description', new Contributte\Translation\Wrappers\Message('form.mail.description', [...]);
```

Or pass the not translatable texts:

```php
$form->addSelect('country', 'form.country.label')
	->setItems([
		new Contributte\Translation\Wrappers\NotTranslate('Czech republic'),
		new Contributte\Translation\Wrappers\NotTranslate('Slovak republic'),
	]);
```

### TranslationProviderInterface

It is possible to pass additional translation resources from your compiler extensions by implementing the `TranslationProviderInterface`.

```php
use Nette\DI\CompilerExtension;
use Contributte\Translation\DI\TranslationProviderInterface;

class MyExtension extends CompilerExtension implements TranslationProviderInterface
{

	public function getTranslationResources(): array
	{
		return [
			__DIR__ . '/../lang/',
		];
	}

}
```

## Development

See [how to contribute](https://contributte.org/contributing.html) to this package.

This package is currently maintained by these authors.

<a href="https://github.com/aleswita">
	<img width="80" height="80" src="https://avatars1.githubusercontent.com/u/6991688?s=80&amp;v=3">
</a>

-----

Consider to [support](https://contributte.org/partners.html) **contributte** development team.
Also thank you for using this package.
