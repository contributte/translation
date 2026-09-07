![](https://heatbadger.now.sh/github/readme/contributte/translation/)

<p align=center>
	<a href="https://github.com/contributte/translation/actions"><img src="https://badgen.net/github/checks/contributte/translation"></a>
	<a href="https://codecov.io/gh/contributte/translation"><img src="https://badgen.net/codecov/c/github/contributte/translation"></a>
	<a href="https://packagist.org/packages/contributte/translation"><img src="https://badgen.net/packagist/v/contributte/translation"></a>
</p>

Symfony Translation integration for Nette Framework.

## Quick start

Install the package and register its extension:

```bash
composer require contributte/translation
```

```neon
extensions:
	translation: Contributte\Translation\DI\TranslationExtension

translation:
	locales:
		default: en
	dirs:
		- %appDir%/lang
```

Create `%appDir%/lang/messages.en.neon` (the filename is `domain.locale.neon`):

```neon
homepage:
	welcome: Welcome!
```

Inject `Nette\Localization\ITranslator` and translate the message, or render it directly in Latte:

```php
$message = $translator->translate('homepage.welcome');
```

```latte
{_homepage.welcome}
```

Both forms render `Welcome!`. See [the detailed documentation](.docs/README.md) for locale resolvers, loaders, and reference material.

## Development

See [how to contribute](https://contributte.org/contributing.html) to this package.
