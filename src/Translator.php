<?php declare(strict_types = 1);

namespace Contributte\Translation;

use Contributte;
use Nette;
use Psr;
use Symfony;

class Translator extends Symfony\Component\Translation\Translator implements Nette\Localization\ITranslator
{

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

	/** @var bool */
	public $returnOriginalMessage = true;

	/** @var string[]|null */
	private $localesWhitelist;

	/** @var string[] */
	private $prefix = [];

	/** @var string[][] @internal */
	private $prefixTemp = [];

	/** @var bool[] @internal */
	private $resourcesLocales = [];

	/** @var Psr\Log\LoggerInterface|null */
	private $psrLogger;

	/** @var Contributte\Translation\Tracy\Panel|null */
	private $tracyPanel;

	/**
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
	 * @param string|null $domain
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

	public function getPsrLogger(): ?Psr\Log\LoggerInterface
	{
		return $this->psrLogger;
	}

	public function setPsrLogger(?Psr\Log\LoggerInterface $psrLogger): self
	{
		$this->psrLogger = $psrLogger;
		return $this;
	}

	public function getTracyPanel(): ?Tracy\Panel
	{
		return $this->tracyPanel;
	}

	public function setTracyPanel(?Tracy\Panel $tracyPanel): self
	{
		$this->tracyPanel = $tracyPanel;
		return $this;
	}

	/**
	 * @param mixed $message
	 * @param mixed ...$parameters
	 */
	public function translate($message, ...$parameters): string
	{
		if ($message === null || $message === '') {
			return '';
		}

		if ($message instanceof Wrappers\NotTranslate) {
			return $message->getMessage();
		}

		if ($message instanceof Wrappers\Message) {
			$parameters = $message->getParameters();
			$message = $message->getMessage();

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

		$originalMessage = $message;

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

		$translated = $this->trans($message, $params, $domain, $locale);

		if ($this->returnOriginalMessage) {
			if ($domain . '.' . $translated === $originalMessage) {
				return $originalMessage;
			}
		}

		return $translated;
	}

	/**
	 * @param string|null $id
	 * @param mixed[] $parameters
	 * @param string|null $domain
	 * @param string|null $locale
	 * @return string
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint
	 */
	public function trans($id, array $parameters = [], $domain = null, $locale = null)
	{
		if ($domain === null) {
			$domain = 'messages';
		}

		if ($id !== null) {
			if ($this->psrLogger !== null) {
				if (!$this->getCatalogue()->has($id, $domain)) {
					$this->psrLogger->notice('Missing translation', [
						'id' => $id,
						'domain' => $domain,
						'locale' => $locale ?? $this->getLocale(),
					]);
				}
			}

			if ($this->tracyPanel !== null) {
				if (!$this->getCatalogue()->has($id, $domain)) {
					$this->tracyPanel->addMissingTranslation($id, $domain);
				}
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
