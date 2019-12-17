<?php declare(strict_types = 1);

namespace Contributte\Translation;

use Psr;

/**
 * @property      Psr\Log\LoggerInterface|null $psrLogger
 */
class LoggerTranslator extends Translator
{

	/** @var Psr\Log\LoggerInterface|null */
	private $psrLogger;

	public function getPsrLogger(): ?Psr\Log\LoggerInterface
	{
		return $this->psrLogger;
	}

	public function setPsrLogger(?Psr\Log\LoggerInterface $psrLogger): self
	{
		$this->psrLogger = $psrLogger;
		return $this;
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
	public function trans(?string $id, array $parameters = [], ?string $domain = null, ?string $locale = null)
	{
		if ($id !== null && $this->psrLogger !== null) {
			if ($domain === null) {
				$domain = 'messages';
			}

			if (!$this->getCatalogue()->has($id, $domain)) {
				$this->psrLogger->notice('Missing translation', [
					'id' => $id,
					'domain' => $domain,
					'locale' => $locale ?? $this->getLocale(),
				]);
			}
		}

		return parent::trans($id, $parameters, $domain, $locale);
	}

}
