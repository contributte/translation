<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

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
	 * {@inheritdoc}
	 */
	public function trans($id, array $parameters = [], $domain = null, $locale = null)
	{
		if ($this->psrLogger !== null) {
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
