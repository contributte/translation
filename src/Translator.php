<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation;

use Contributte;
use Nette;
use Symfony;


/**
 * @property-read Contributte\Translation\LocaleResolver $localeResolver
 * @property-read Contributte\Translation\FallbackResolver $fallbackResolver
 * @property-read string $defaultLocale
 * @property-read string|null $cacheDir
 * @property-read bool $debug
 * @property-read Contributte\Translation\Tracy\Panel|null $tracyPanel
 * @property      array|null $localesWhitelist
 * @property      array $prefix
 * @property-read array $prefixTemp
 * @property-read string $formattedPrefix
 * @property-read string[] $availableLocales
 * @property      string|null $locale
 *
 * @author Ales Wita
 * @author Filip Prochazka
 */
class Translator extends Symfony\Component\Translation\Translator implements Nette\Localization\ITranslator
{
	use Nette\SmartObject;
	use KdybyTranslationBackCompatibilityTrait;

	/** @var Contributte\Translation\LocaleResolver */
	private $localeResolver;

	/** @var Contributte\Translation\FallbackResolver */
	private $fallbackResolver;

	/** @var string */
	private $defaultLocale;

	/** @var string|null */
	private $cacheDir;

	/** @var bool */
	private $debug;

	/** @var Contributte\Translation\Tracy\Panel|null */
	private $tracyPanel;

	/** @var array|null */
	private $localesWhitelist;

	/** @var array */
	private $prefix = [];

	/** @var array @internal */
	private $prefixTemp = [];

	/** @var array @internal */
	private $resourcesLocales = [];


	/**
	 * @param Contributte\Translation\LocaleResolver $localeResolver
	 * @param Contributte\Translation\FallbackResolver $fallbackResolver
	 * @param string $defaultLocale
	 * @param string|null $cacheDir
	 * @param bool $debug
	 */
	public function __construct(LocaleResolver $localeResolver, FallbackResolver $fallbackResolver, string $defaultLocale, string $cacheDir = null, bool $debug = false)
	{
		$this->localeResolver = $localeResolver;
		$this->fallbackResolver = $fallbackResolver;
		$this->assertValidLocale($defaultLocale);
		$this->defaultLocale = $defaultLocale;
		$this->cacheDir = $cacheDir;
		$this->debug = $debug;

		parent::__construct('', null, $cacheDir, $debug);
		$this->setLocale(null);
	}


	/**
	 * @return Contributte\Translation\LocaleResolver
	 */
	public function getLocaleResolver(): LocaleResolver
	{
		return $this->localeResolver;
	}


	/**
	 * @return Contributte\Translation\FallbackResolver
	 */
	public function getFallbackResolver(): FallbackResolver
	{
		return $this->fallbackResolver;
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
	 * @return Contributte\Translation\Tracy\Panel|null
	 */
	public function getTracyPanel(): ?Tracy\Panel
	{
		return $this->tracyPanel;
	}


	/**
	 * @param Contributte\Translation\Tracy\Panel|null $tracyPanel
	 * @return self
	 */
	public function setTracyPanel(?Tracy\Panel $tracyPanel): self
	{
		$this->tracyPanel = $tracyPanel;
		return $this;
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
	 * @return array
	 */
	public function getPrefix(): array
	{
		return $this->prefix;
	}


	/**
	 * @param array $array
	 * @return self
	 */
	public function setPrefix(array $array): self
	{
		$this->prefixTemp[] = $this->prefix;
		$this->prefix = $array;
		return $this;
	}


	/**
	 * @internal
	 *
	 * @return array
	 */
	public function getPrefixTemp(): array
	{
		$temp = end($this->prefixTemp);
		array_pop($this->prefixTemp);
		return $temp !== false ? $temp : [];
	}


	/**
	 * @param string $string
	 * @return self
	 */
	public function addPrefix(string $string): self
	{
		$this->prefix[] = $string;
		return $this;
	}


	/**
	 * @param string|null $string
	 * @return self
	 * @throws Contributte\Translation\InvalidArgumentException
	 */
	public function removePrefix(string $string = null): self
	{
		if ($string === null) {
			$key = array_pop($this->prefix);

			if ($key === null) {
				throw new InvalidArgumentException('Can not remove empty prefix.');
			}

		} else {
			$key = array_search($string, array_reverse($this->prefix), true);

			if ($key === false) {
				throw new InvalidArgumentException('Unknown "' . $string . '" prefix.');
			}

			unset($key);
		}

		return $this;
	}


	/**
	 * @return string
	 */
	public function getFormattedPrefix(): string
	{
		return implode('.', $this->prefix);
	}


	/**
	 * @param string $prefix
	 * @return Contributte\Translation\PrefixedTranslator
	 */
	public function createPrefixedTranslator(string $prefix): PrefixedTranslator
	{
		return new PrefixedTranslator($this, $prefix);
	}


	/**
	 * @return string[]
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
	public function setLocale($locale)
	{
		parent::setLocale($locale);
	}


	/**
	 * {@inheritdoc}
	 */
	public function setFallbackLocales(array $locales)
	{
		parent::setFallbackLocales($locales);
		$this->fallbackResolver->setFallbackLocales($locales);
	}


	/**
	 * {@inheritdoc}
	 */
	public function translate($message, ...$parameters): string
	{
		$count = array_key_exists(0, $parameters) ? $parameters[0] : null;
		$params = array_key_exists(1, $parameters) ? $parameters[1] : [];
		$domain = array_key_exists(2, $parameters) ? $parameters[2] : null;
		$locale = array_key_exists(3, $parameters) ? $parameters[3] : null;

		if (is_array($count)) {
			$locale = $domain !== null ? (string) $domain : null;
			$domain = $params !== null && !empty($params) ? (string) $params : null;
			$params = $count;
			$count = null;
		}

		if (Nette\Utils\Strings::startsWith($message, '//')) {
			$message = Nette\Utils\Strings::substring($message, 2);

		} elseif (count($this->prefix) > 0) {
			$message = $this->getFormattedPrefix() . '.' . $message;
		}

		if ($domain === null) {
			[$domain, $message] = Helpers::extractMessage($message);
		}

		$tmp = [];
		foreach ($params as $k1 => $v1) {
			$tmp['%' . $k1 . '%'] = $v1;
		}
		$params = $tmp;

		if (Nette\Utils\Validators::isNumericInt($count)) {
			$params += ['%count%' => (int) $count];
		}

		return $this->trans($message, $params, $domain, $locale);
	}


	/**
	 * {@inheritdoc}
	 */
	public function trans($id, array $parameters = [], $domain = null, $locale = null)
	{
		if ($this->tracyPanel !== null) {
			if ($domain === null) {
				$domain = 'messages';
			}

			if ($id !== null && !$this->getCatalogue()->has($id, $domain)) {
				$this->tracyPanel->addMissingTranslation($id, $domain);
			}
		}

		return parent::trans($id, $parameters, $domain, $locale);
	}


	/**
	 * {@inheritdoc}
	 */
	protected function computeFallbackLocales($locale)
	{
		return $this->fallbackResolver->compute($this, $locale);
	}
}
