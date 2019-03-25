<?php

/**
 * This file is part of the Translette\Translation
 */

declare(strict_types=1);

namespace Translette\Translation;

use Nette;
use Symfony;
use Translette;


/**
 * @property-read Translette\Translation\LocaleResolver $localeResolver
 * @property-read string $defaultLocale
 * @property-read string|null $cacheDir
 * @property-read bool $debug
 * @property-read Symfony\Component\Translation\Formatter\MessageFormatterInterface $messageFormatter
 * @property      array|null $localesWhitelist
 * @property-read array $availableLocales
 * @property      string|null $locale
 *
 * @author Ales Wita
 * @author Filip Prochazka
 */
class Translator extends Symfony\Component\Translation\Translator implements Nette\Localization\ITranslator
{
	use Nette\SmartObject;

	/** @var Translette\Translation\LocaleResolver */
	private $localeResolver;

	/** @var string */
	private $defaultLocale;

	/** @var string|null */
	private $cacheDir;

	/** @var bool */
	private $debug;

	/** @var array|null */
	private $localesWhitelist;

	/** @var array|null */
	private $resourcesLocales;
	

	/**
	 * @param Translette\Translation\LocaleResolver $localeResolver
	 * @param $defaultLocale
	 * @param string|null $cacheDir
	 * @param bool $debug
	 */
	public function __construct(LocaleResolver $localeResolver, string $defaultLocale, string $cacheDir = null, bool $debug = false)
	{
		$this->localeResolver = $localeResolver;
		$this->assertValidLocale($defaultLocale);
		$this->defaultLocale = $defaultLocale;
		$this->cacheDir = $cacheDir;
		$this->debug = $debug;

		parent::__construct('', null, $cacheDir, $debug);
		$this->setLocale(null);
	}


	/**
	 * @return Translette\Translation\LocaleResolver
	 */
	public function getLocaleResolver(): LocaleResolver
	{
		return $this->localeResolver;
	}


	/**
	 * @return string|null
	 */
	public function getCacheDir(): ?string
	{
		return $this->cacheDir;
	}


	/**
	 * @return bool
	 */
	public function getDebug(): bool
	{
		return $this->debug;
	}


	/**
	 * @return array|null
	 */
	public function getLocalesWhitelist(): ?array
	{
		return $this->localesWhitelist;
	}


	/**
	 * @param array|null $whitelist
	 * @return self
	 */
	public function setLocalesWhitelist(?array $whitelist): self
	{
		$this->localesWhitelist = $whitelist;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getDefaultLocale(): string
	{
		return $this->defaultLocale;
	}


	/**
	 * @return array
	 */
	public function getAvailableLocales(): array
	{
		$locales = array_keys($this->resourcesLocales);
		sort($locales);
		return $locales;
	}


	/**
	 * {@inheritdoc}
	 */
	public function addResource($format, $resource, $locale, $domain = null)
	{
		parent::addResource($format, $resource, $locale, $domain);
		$this->resourcesLocales[$locale] = true;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getLocale()
	{
		if (parent::getLocale() === null) {
			$this->setLocale($this->localeResolver->resolve($this));
		}

		return parent::getLocale();
	}


	/**
	 * {@inheritdoc}
	 */
	//function translate($message, ...$parameters): string;
	public function translate($message, $count = null, $parameters = [], $domain = null, $locale = null)
	{
		if (is_array($count)) {// back compatibility for ITranslator
			$locale = $domain !== null ? (string) $domain : null;
			$domain = $parameters !== null && !empty($parameters) ? (string) $parameters : null;
			$parameters = $count;
			$count = null;
		}

		if ($domain === null) {
			[$domain, $message] = Helpers::extractMessage($message);
		}

		$tmp = [];
		foreach ($parameters as $k1 => $v1) {
			//$tmp['%' . Nette\Utils\Strings::trim($k1, '%') . '%'] = $v1;// need this?
			$tmp['%' . $k1 . '%'] = $v1;
		}
		$parameters = $tmp;

		if (Nette\Utils\Validators::isNumericInt($count)) {
			$parameters += ['%%count%%' => (int) $count];
		}

		return $this->trans($message, $parameters, $domain, $locale);
	}
}
