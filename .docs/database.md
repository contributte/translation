# Database loaders
Package included database loader for **[Doctrine 2](https://www.doctrine-project.org/)** and **[Nette Database 3](https://doc.nette.org/cs/3.0/database)**.

## Content
- [Doctrine - how to configure](#doctrine)
- [Nette Database - how to configure](#nette-database)
- [Bugs - known bugs](#bugs)

## Doctrine
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
use Nette;


/**
 * @property-read int $messageId
 * @property-read string $id
 * @property-read string $locale
 * @property-read string $message
 *
 * @ORM\Entity
 * @ORM\Table(name="messages")
 */
class Messages
{
	use Nette\SmartObject;

	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(type="integer", nullable=false)
	 * @ORM\GeneratedValue
	 */
	private $messageId;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=false)
	 */
	private $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=false)
	 */
	private $locale;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=false)
	 */
	private $message;


	/**
	 * @return int
	 */
	public function getMessageId(): int
	{
		return $this->messageId;
	}


	/**
	 * @return string
	 */
	public function getId(): string
	{
		return $this->id;
	}


	/**
	 * @return string
	 */
	public function getLocale(): string
	{
		return $this->locale;
	}


	/**
	 * @return string
	 */
	public function getMessage(): string
	{
		return $this->message;
	}
}
```

## Nette Database
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

## Bugs
- refreshing cache, if you something change in database
