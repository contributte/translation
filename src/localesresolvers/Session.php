<?php

/**
 * This file is part of the Translette\Translation
 */

declare(strict_types=1);

namespace Translette\Translation\LocalesResolvers;

use Nette;
use Translette;


/**
 * @author Ales Wita
 * @author Filip Prochazka
 */
class Session implements ResolverInterface
{
	use Nette\SmartObject;

	/** @var Nette\Http\IResponse */
	private $httpResponse;

	/** @var Nette\Http\Session */
	private $session;

	/** @var Nette\Http\SessionSection */
	private $sessionSection;


	/**
	 * @param Nette\Http\IResponse $httpResponse
	 * @param Nette\Http\Session $session
	 */
	public function __construct(Nette\Http\IResponse $httpResponse, Nette\Http\Session $session)
	{
		$this->httpResponse = $httpResponse;
		$this->session = $session;
		$this->sessionSection = $session->getSection(get_class($this));
	}


	/**
	 * @param string $locale
	 * @return self
	 */
	public function setLocale(string $locale = null): self
	{
		$this->sessionSection->locale = $locale;
		return $this;
	}


	/**
	 * @param Translette\Translation\Translator $translator
	 * @return string|null
	 */
	public function resolve(Translette\Translation\Translator $translator): ?string
	{
		if (!$this->session->isStarted() && $this->httpResponse->isSent()) {
			trigger_error('The advice of session locale resolver is required but the session has not been started and headers had been already sent. Either start your sessions earlier or disabled the SessionResolver.', E_USER_WARNING);
			return null;
		}

		if (!isset($this->sessionSection->locale)) {
			return null;
		}

		if (!in_array(Nette\Utils\Strings::substring($this->sessionSection->locale, 0, 2), array_map(function ($locale) {return Nette\Utils\Strings::substring($locale, 0, 2);}, $translator->availableLocales), true)) {
			return null;
		}

		return $this->sessionSection->locale;
	}
}
