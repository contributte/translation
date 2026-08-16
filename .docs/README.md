# Translation reference

The root README contains the first-success setup. This reference covers the advanced integration surface.

## Locale resolvers

Configure resolver order with `translation.localeResolvers`. Available resolvers are `Router`, `Header`, `Parameter`, and `Session` from `Contributte\Translation\LocalesResolvers`; the default key is `locale`.

## Message files and parameters

Message files use `domain.locale.format`, for example `messages.en_US.neon`. Parameters are passed without percent signs:

```neon
user.name.taken: "Sorry, the username %name% is already taken."
```

```latte
{_user.name.taken, [name => 'Ales']}
```

## Loaders

NEON is configured by default. File loaders can be registered through `translation.loaders`; supported Symfony formats include array, CSV, INI, JSON, MO, PHP, PO, TS, XLIFF, and YAML. Doctrine and Nette Database loaders use marker files such as `messages.en_US.doctrine` and `messages.en_US.nettedatabase`.

## Integration helpers

Use `Wrappers\Message` for translatable form options and `Wrappers\NotTranslate` for literal values. Compiler extensions can provide additional scanned directories by implementing `TranslationProviderInterface`.
