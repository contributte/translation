<?php declare(strict_types = 1);

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
 * @property      string[]|null $localesWhitelist
 * @property      string[] $prefix
 * @property-read string[][] $prefixTemp
 * @property-read string $formattedPrefix
 * @property-read string[] $availableLocales
 * @property      string|null $locale
 */
class Translator extends Symfony\Component\Translation\Translator implements Nette\Localization\ITranslator
{

	use Nette\SmartObject;

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

	/** @var string[]|null */
	private $localesWhitelist;

	/** @var string[] */
	private $prefix = [];

	/** @var string[][] @internal */
	private $prefixTemp = [];

	/** @var bool[] @internal */
	private $resourcesLocales = [];

	/**
	 * Translator constructor.
	 *
	 * @param LocaleResolver $localeResolver
	 * @param FallbackResolver $fallbackResolver
	 * @param string $defaultLocale
	 * @param string|null $cacheDir
	 * @param bool $debug
	 * @param string[] $cacheVary
	 */
	public function __construct(LocaleResolver $localeResolver, FallbackResolver $fallbackResolver, string $defaultLocale, ?string $cacheDir = null, bool $debug = false, array $cacheVary = [])
	{
		$this->localeResolver = $localeResolver;
		$this->fallbackResolver = $fallbackResolver;
		$this->assertValidLocale($defaultLocale);
		$this->defaultLocale = $defaultLocale;
		$this->cacheDir = $cacheDir;
		$this->debug = $debug;

		parent::__construct('', null, $cacheDir, $debug, $cacheVary);
	}

	public function getLocaleResolver(): LocaleResolver
	{
		return $this->localeResolver;
	}

	public function getFallbackResolver(): FallbackResolver
	{
		return $this->fallbackResolver;
	}

	public function getDefaultLocale(): string
	{
		return $this->defaultLocale;
	}

	public function getCacheDir(): ?string
	{
		return $this->cacheDir;
	}

	public function getDebug(): bool
	{
		return $this->debug;
	}

	/**
	 * @return string[]|null
	 */
	public function getLocalesWhitelist(): ?array
	{
		return $this->localesWhitelist;
	}

	/**
	 * @param string[]|null $whitelist
	 */
	public function setLocalesWhitelist(?array $whitelist): self
	{
		$this->localesWhitelist = $whitelist;
		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getPrefix(): array
	{
		return $this->prefix;
	}

	/**
	 * @param string[] $array
	 */
	public function setPrefix(array $array): self
	{
		$this->prefixTemp[] = $this->prefix;
		$this->prefix = $array;
		return $this;
	}

	/**
	 * @return string[]
	 * @internal
	 */
	public function getPrefixTemp(): array
	{
		$temp = end($this->prefixTemp);
		array_pop($this->prefixTemp);
		return $temp !== false ? $temp : [];
	}

	public function addPrefix(string $string): self
	{
		$this->prefix[] = $string;
		return $this;
	}

	/**
	 * @throws Contributte\Translation\Exceptions\InvalidArgument
	 */
	public function removePrefix(?string $string = null): self
	{
		if ($string === null) {
			$value = array_pop($this->prefix);

			if ($value === null) {
				throw new Exceptions\InvalidArgument('Can not remove empty prefix.');
			}

		} else {
			$key = array_search($string, array_reverse($this->prefix), true);

			if ($key === false) {
				throw new Exceptions\InvalidArgument('Unknown "' . $string . '" prefix.');
			}

			unset($this->prefix[$key]);
		}

		return $this;
	}

	public function getFormattedPrefix(): string
	{
		return implode('.', $this->prefix);
	}

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
	 * @param string $format
	 * @param mixed $resource
	 * @param string $locale
	 * @param string $domain
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint
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
		if (parent::getLocale() === '') {
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
	 * @param mixed $message
	 * @param mixed ...$parameters
	 */
	public function translate($message, ...$parameters): string
	{
		if ($message === null) {
			return '';

		} elseif ($message instanceof Wrappers\NotTranslate) {
			return $message->message;

		} elseif ($message instanceof Wrappers\Message) {
			$parameters = $message->parameters;
			$message = $message->message;

		} elseif (is_int($message)) {// float type can be confused for dot inside
			$message = (string) $message;
		}

		if (!is_string($message)) {
			throw new Exceptions\InvalidArgument('Message must be string, ' . gettype($message) . ' given.');
		}

		$count = array_key_exists(0, $parameters) ? $parameters[0] : null;
		$params = array_key_exists(1, $parameters) ? $parameters[1] : [];
		$domain = array_key_exists(2, $parameters) ? $parameters[2] : null;
		$locale = array_key_exists(3, $parameters) ? $parameters[3] : null;

		if (is_array($count)) {
			$locale = $domain !== null ? (string) $domain : null;
			$domain = $params !== null && $params !== [] ? (string) $params : null;
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

		if (Nette\Utils\Validators::isNumeric($count)) {
			$params += ['%count%' => $count];
		}

		return $this->trans($message, $params, $domain, $locale);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function computeFallbackLocales($locale)
	{
		return $this->fallbackResolver->compute($this, $locale);
	}

}
