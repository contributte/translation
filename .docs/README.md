# Translation

## Content
- [Usage - how to register](#usage)
- [Configuration - how to configure](#configuration)
- [Presenter - example](#presenter)
- [Latte - example](#latte)
- [Neon - example](#neon)
- [Database loaders](#database-loaders)

## Usage
Added translation extension.
```neon
extensions:
	translation: Contributte\Translation\DI\TranslationExtension
```

## Configuration
Basic configuration.
```neon
translation:
	locales:
		whitelist: [en, cs, sk]
		default: en
	dirs:
		- %appDir%/lang
```

## Presenter
How to use in backend.
```php
<?php

declare(strict_types=1);

namespace App;

use Nette;
use Contributte;


class BasePresenter extends Nette\Application\UI\Presenter
{
	/** @var Nette\Localization\ITranslator @inject */
	public $translator;

	/** @var Contributte\Translation\LocalesResolvers\Session @inject */
	public $translatorSessionResolver;


	/**
	 * @param string $locale
	 */
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

## Latte
How to use in frontend.
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

## Neon
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
```neon
prefix:
	for: "message" # messages.prefix.for
```

## Database loaders
Package included database loader for **[Doctrine 2](https://www.doctrine-project.org/)** and **[Nette Database 3](https://doc.nette.org/cs/3.0/database)**.

### Doctrine
You must create a file with specific format in scanned dirs like as **messages.en_US.doctrine**. All parameters are optional, but file must be created.

```neon
table: "My\Entity" # if you specify entity key, "messages" from file name will be ignored
id: "id" # id column name, default is "id"
locale: "locale" # locale column name, default is "locale"
message: "message" # message column name, default is "message"
```

Added loader to translation configuration.
```neon
translation:
	loaders:
		doctrine: Contributte\Translation\Loaders\Doctrine
```

Entity example.
```php
<?php

declare(strict_types=1);

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

```neon
table: "my_table" # if you specify table key, "messages" from file name will be ignored
id: "id" # id column name, default is "id"
locale: "locale" # locale column name, default is "locale"
message: "message" # message column name, default is "message"
```

Added loader to translation configuration.
```neon
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
