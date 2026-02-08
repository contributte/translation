<?php declare(strict_types = 1);

namespace Contributte\Translation;

use Contributte\Translation\Exceptions\InvalidArgument;
use Contributte\Translation\Tracy\Panel;
use Contributte\Translation\Wrappers\Message;
use Contributte\Translation\Wrappers\NotTranslate;
use Generator;
use Nette\Localization\Translator as NetteTranslator;
use Nette\Utils\Strings;
use Nette\Utils\Validators;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Translator as SymfonyTranslator;

class Translator extends SymfonyTranslator implements NetteTranslator
{

	private LocaleResolver $localeResolver;

	private FallbackResolver $fallbackResolver;

	private string $defaultLocale;

	private ?string $cacheDir;

	private bool $debug;

	public bool $returnOriginalMessage = true;

	/** @var array<string>|null */
	private ?array $localesWhitelist = null;

	/** @var array<string> */
	private array $prefix = [];

	/** @var array<array<string>> */
	private array $prefixTemp = [];

	/** @var array<bool> */
	private array $resourcesLocales = [];

	private ?LoggerInterface $psrLogger = null;

	private ?Panel $tracyPanel = null;

	private string $initLang;

	/** @var callable(mixed[] $params): Generator|null */
	private $translateKeyGenerator = null;

	/**
	 * @param array<string> $cacheVary
	 */
	public function __construct(
		LocaleResolver $localeResolver,
		FallbackResolver $fallbackResolver,
		string $defaultLocale,
		?string $cacheDir = null,
		bool $debug = false,
		array $cacheVary = []
	)
	{
		$this->localeResolver = $localeResolver;
		$this->fallbackResolver = $fallbackResolver;
		$this->assertValidLocale($defaultLocale);
		$this->defaultLocale = $defaultLocale;
		$this->cacheDir = $cacheDir;
		$this->debug = $debug;
		$this->initLang = Strings::replace(self::class, '~\\\\~');

		parent::__construct($this->initLang, null, $cacheDir, $debug, $cacheVary);
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
	 * @return array<string>|null
	 */
	public function getLocalesWhitelist(): ?array
	{
		return $this->localesWhitelist;
	}

	/**
	 * @param array<string>|null $whitelist
	 */
	public function setLocalesWhitelist(
		?array $whitelist
	): self
	{
		$this->localesWhitelist = $whitelist;

		return $this;
	}

	/**
	 * @return array<string>
	 */
	public function getPrefix(): array
	{
		return $this->prefix;
	}

	/**
	 * @param array<string> $array
	 */
	public function setPrefix(
		array $array
	): self
	{
		$this->prefixTemp[] = $this->prefix;
		$this->prefix = $array;

		return $this;
	}

	/**
	 * @return array<string>
	 * @internal
	 */
	public function getPrefixTemp(): array
	{
		$temp = end($this->prefixTemp);
		array_pop($this->prefixTemp);

		return $temp !== false ? $temp : [];
	}

	public function addPrefix(
		string $string
	): self
	{
		$this->prefix[] = $string;
		return $this;
	}

	public function getTranslateKeyGenerator(): callable
	{
		if ($this->translateKeyGenerator === null) {
			$this->translateKeyGenerator = function (array $params): Generator {
				foreach ($params as $k1 => $v1) {
					yield '%' . $k1 . '%' => $v1;
				}
			};
		}

		return $this->translateKeyGenerator;
	}

	public function setTranslateKeyGenerator(
		callable $generator
	): self
	{
		$this->translateKeyGenerator = $generator;
		return $this;
	}

	/**
	 * @throws \Contributte\Translation\Exceptions\InvalidArgument
	 */
	public function removePrefix(
		?string $string = null
	): self
	{
		if ($string === null) {
			$value = array_pop($this->prefix);

			if ($value === null) {
				throw new InvalidArgument('Can not remove empty prefix.');
			}
		} else {
			$key = array_search($string, array_reverse($this->prefix), true);

			if ($key === false) {
				throw new InvalidArgument('Unknown "' . $string . '" prefix.');
			}

			unset($this->prefix[$key]);
		}

		return $this;
	}

	public function getFormattedPrefix(): string
	{
		return implode('.', $this->prefix);
	}

	public function createPrefixedTranslator(
		string $prefix
	): PrefixedTranslator
	{
		return new PrefixedTranslator($this, $prefix);
	}

	/**
	 * @return array<string>
	 */
	public function getAvailableLocales(): array
	{
		$locales = array_keys($this->resourcesLocales);
		sort($locales);
		return $locales;
	}

	public function addResource(
		string $format,
		$resource,
		string $locale,
		?string $domain = null
	): void
	{
		parent::addResource($format, $resource, $locale, $domain);

		$this->resourcesLocales[$locale] = true;
	}

	public function getLocale(): string
	{
		if (parent::getLocale() === $this->initLang) {
			$this->setLocale($this->localeResolver->resolve($this));
		}

		return parent::getLocale();
	}

	/**
	 * @param array<string> $locales
	 */
	public function setFallbackLocales(
		array $locales
	): void
	{
		parent::setFallbackLocales($locales);

		$this->fallbackResolver
			->setFallbackLocales($locales);
	}

	public function getPsrLogger(): ?LoggerInterface
	{
		return $this->psrLogger;
	}

	public function setPsrLogger(
		?LoggerInterface $psrLogger
	): self
	{
		$this->psrLogger = $psrLogger;
		return $this;
	}

	public function getTracyPanel(): ?Panel
	{
		return $this->tracyPanel;
	}

	public function setTracyPanel(
		?Panel $tracyPanel
	): self
	{
		$this->tracyPanel = $tracyPanel;
		return $this;
	}

	/**
	 * @param mixed $message
	 * @param mixed ...$parameters
	 */
	public function translate(
		$message,
		...$parameters
	): string
	{
		if ($message === null || $message === '') {
			return '';
		}

		if ($message instanceof NotTranslate) {
			return $message->message;
		}

		if ($message instanceof Message) {
			$parameters = $message->parameters;
			$message = $message->message;

		} elseif (is_int($message)) {// float type can be confused for dot inside
			$message = (string) $message;
		}

		if (!is_string($message)) {
			throw new InvalidArgument('Message must be string, ' . gettype($message) . ' given.');
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

		if (Helpers::isAbsoluteMessage($message)) {
			$message = Strings::substring($message, 2);

		} elseif (count($this->prefix) > 0) {
			$message = $this->getFormattedPrefix() . '.' . $message;
		}

		if ($domain === null) {
			[$domain, $message] = Helpers::extractMessage($message);
		}

		$generator = $this->getTranslateKeyGenerator();

		$params = iterator_to_array($generator($params));

		if (Validators::isNumeric($count)) {
			$params += ['%count%' => $count];
		}

		$translated = $this->trans($message, $params, $domain, $locale);

		if ($this->returnOriginalMessage) {
			if (!$this->getCatalogue($locale)->has($message, $domain)) {
				return $originalMessage;
			}
		}

		return $translated;
	}

	/**
	 * @param array<mixed> $parameters
	 */
	public function trans(
		?string $id,
		array $parameters = [],
		?string $domain = null,
		?string $locale = null
	): string
	{
		if ($domain === null) {
			$domain = 'messages';
		}

		if ($id !== null) {
			if ($this->psrLogger !== null) {
				if (!$this->getCatalogue($locale)->has($id, $domain)) {
					$this->psrLogger
						->notice(
							'Missing translation',
							[
								'id' => $id,
								'domain' => $domain,
								'locale' => $locale ?? $this->getLocale(),
							]
						);
				}
			}

			if ($this->tracyPanel !== null) {
				if (!$this->getCatalogue($locale)->has($id, $domain)) {
					$this->tracyPanel
						->addMissingTranslation($id, $domain);
				}
			}
		}

		return parent::trans(
			$id,
			$parameters,
			$domain,
			$locale
		);
	}

	/**
	 * @return array<string>
	 */
	protected function computeFallbackLocales(
		string $locale
	): array
	{
		return $this->fallbackResolver
			->compute(
				$this,
				$locale
			);
	}

}
