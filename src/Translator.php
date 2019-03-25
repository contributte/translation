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
 * @property-read Translette\Translation\Tracy\Panel|null $tracyPanel
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

	/** @var Translette\Translation\Tracy\Panel|null */
	private $tracyPanel;

	/** @var array|null */
	private $localesWhitelist;

	/** @var array|null */
	private $resourcesLocales;

	/** @var array */
	private $fallbackLocales = [];


	/**
	 * @param Translette\Translation\LocaleResolver $localeResolver
	 * @param $defaultLocale
	 * @param string|null $cacheDir
	 * @param bool $debug
	 * @param Translette\Translation\Tracy\Panel|null $tracyPanel
	 */
	public function __construct(LocaleResolver $localeResolver, string $defaultLocale, string $cacheDir = null, bool $debug = false, ?Tracy\Panel $tracyPanel = null)
	{
		$this->localeResolver = $localeResolver;
		$this->assertValidLocale($defaultLocale);
		$this->defaultLocale = $defaultLocale;
		$this->cacheDir = $cacheDir;
		$this->debug = $debug;
		$this->tracyPanel = $tracyPanel;

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
	 * @return string
	 */
	public function getDefaultLocale(): string
	{
		return $this->defaultLocale;
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
	 * @return Tracy\Panel|null
	 */
	public function getTracyPanel(): ?Tracy\Panel
	{
		return $this->tracyPanel;
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
		if ($this->tracyPanel !== null) {
			$this->tracyPanel->setLocalesWhitelist($whitelist);
		}

		$this->localesWhitelist = $whitelist;
		return $this;
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
		if ($this->tracyPanel !== null) {
			if ($this->localesWhitelist !== null && Nette\Utils\Strings::match($locale, Helpers::whitelistRegexp($this->localesWhitelist))) {
				$this->tracyPanel->addResource($format, $resource, $locale, $domain);

			} else {
				$this->tracyPanel->addIgnoredResource($format, $resource, $locale, $domain);
				return;
			}
		}

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
	public function setFallbackLocales(array $locales)
	{
		parent::setFallbackLocales($locales);
		$this->fallbackLocales = $locales;
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


	/**
	 * {@inheritdoc}
	 */
	protected function computeFallbackLocales($locale)
	{
		$locales = [];
		foreach ($this->fallbackLocales as $fallback) {
			if ($fallback === $locale) {
				continue;
			}

			$locales[] = $fallback;
		}

		if (strrchr($locale, '_') !== false) {
			array_unshift($locales, substr($locale, 0, -strlen(strrchr($locale, '_'))));
		}

		foreach ($this->getAvailableLocales() as $v1) {
			if ($v1 === $locale) {
				continue;
			}

			if (substr($v1, 0, 2) === substr($locale, 0, 2)) {
				array_unshift($locales, $v1);
				break;
			}
		}

		return array_unique($locales);
	}
}
