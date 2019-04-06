# Database loaders
Package included database loader for **[Doctrine 2](https://www.doctrine-project.org/)**.

## Content
- [Doctrine - how to configure](#doctrine)
- [Bugs - known bugs](#bugs)

## Doctrine
You must create a file with specific format in scanned dirs like as **MyEntity.en_US.doctrine**. All parameters are optional, but file must be created.

```neon
entity: App\Entity\Translations # if you specify entity key, MyEntity from file name will be ignored
id: "id" # id column name, default is "id"
locale: "locale" # locale column name, default is "locale"
message: "message" # message column name, default is "message"
timestamp: "timestamp" # timestamp column name, default is "timestamp"
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
 * @property-read int $translationId
 * @property-read string $id
 * @property-read string $locale
 * @property-read string $message
 * @property-read int $timestamp
 *
 * @ORM\Entity
 * @ORM\Table(name="translations")
 */
class Translations
{
	use Nette\SmartObject;

	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(type="integer", nullable=false)
	 * @ORM\GeneratedValue
	 */
	private $translationId;

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
	 * @var int
	 *
	 * @ORM\Column(type="integer", nullable=false)
	 */
	private $timestamp;


	/**
	 * @return int
	 */
	public function getTranslationId(): int
	{
		return $this->translationId;
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


	/**
	 * @return int
	 */
	public function getTimestamp(): int
	{
		return $this->timestamp;
	}
}
```

## Bugs
- refreshing cache, if you something change in database or in configuration file
