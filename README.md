# Translation
Symfony\Translation integration for [Nette Framework](https://nette.org).

[![Build Status](https://travis-ci.org/translette/translation.svg?branch=master)](https://travis-ci.org/translette/translation)

## Installation
The best way to install Translette\Translation is using [Composer](http://getcomposer.org/):
```sh
$ composer require translette/translation
```

## Documentation
```neon
extensions:
	translation: Translette\Translation\DI\TranslationExtension

translation:
	locales:
		whitelist:
			- en
			- sk
		default: en
	dirs:
		- %appDir%/lang
```

```php
declare(strict_types=1);

namespace App;

use Nette;


class BasePresenter extends Nette\Application\UI\Presenter
{
	/** @var Nette\Localization\ITranslator @inject */
	public $translator;

	/** @var Translette\Translation\LocalesResolvers\Session @inject */
	public $translatorSessionResolver;


	/**
	 * @param string $locale
	 */
	public function handleChangeLocale(string $locale): void
	{
		$this->translatorSessionResolver->setLocale($locale);
		$this->redirect('this');
	}
}
```
