# Translation

## Content
- [Setup](.docs/README.md#setup)
- [Configuration](.docs/README.md#configuration)
- [Examples](.docs/README.md#examples)
	- [Presenter](#presenter)
    - [Model](#model)
	- [Latte](#latte)
	- [Neon](#neon)
- [Database loaders](#database-loaders)
- [Alternative loaders](#alternative-loaders)
- [Features](#features)

## Setup

Require package

```bash
composer require contributte/translation
```

Register extension


```yaml
extensions:
    translation: Contributte\Translation\DI\TranslationExtension
```

## Configuration

Basic configuration.

```yaml
translation:
    locales:
        whitelist: [en, cs, sk]
        default: en
    dirs:
        - %appDir%/lang
```

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

```smarty
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

File name format.

```
        locale
          |
         /--\
messages.en_US.neon
\______/       \__/
   |            |
 domain     extension
```

File content format.

```yaml
prefix:
    for: "message" # messages.prefix.for
```

## Database loaders

Package included database loaders for **[Doctrine 2](https://www.doctrine-project.org/)** and **[Nette Database 3](https://doc.nette.org/cs/3.0/database)**.

## Alternative loaders

```yaml
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

### Doctrine

You must create a file with specific format in scanned dirs like as **messages.en_US.doctrine**. All parameters are optional, but file must be created.

```yaml
table: "My\Entity" # if you specify entity key, "messages" from file name will be ignored
id: "id" # id column name, default is "id"
locale: "locale" # locale column name, default is "locale"
message: "message" # message column name, default is "message"
```

Added loader to translation configuration.

```yaml
translation:
    loaders:
        doctrine: Contributte\Translation\Loaders\Doctrine
```

Entity example.

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

### Nette Database

You must create a file with specific format in scanned dirs like as **messages.en_US.nettedatabase**. All parameters are optional, but file must be created.

```yaml
table: "my_table" # if you specify table key, "messages" from file name will be ignored
id: "id" # id column name, default is "id"
locale: "locale" # locale column name, default is "locale"
message: "message" # message column name, default is "message"
```

Added loader to translation configuration.

```yaml
translation:
    loaders:
        nettedatabase: Contributte\Translation\Loaders\NetteDatabase
```

DB table example.

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

Possibility passing pluralization to components without pre-translation and avoiding double translation.

```php
$form = new Nette\Application\UI\Form;

$form->addText('mail', 'form.mail.label')
    ->setOption('description', new Contributte\Translation\Wrappers\Message('form.mail.description', [...]);
```

Or pass the not translatable texts.

```php
$form->addSelect('country', 'form.country.label')
    ->setItems([
        new Contributte\Translation\Wrappers\NotTranslate('Czech republic'),
        new Contributte\Translation\Wrappers\NotTranslate('Slovak republic'),
    ]);
```
