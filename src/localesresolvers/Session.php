<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation\LocalesResolvers;

use Contributte;
use Nette;


/**
 * @author Ales Wita
 * @author Filip Prochazka
 */
class Session implements ResolverInterface
{
	use Nette\SmartObject;

	/** @var string */
	public static $parameter = 'locale';

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
		$this->sessionSection[self::$parameter] = $locale;
		return $this;
	}


	/**
	 * @param Contributte\Translation\Translator $translator
	 * @return string|null
	 */
	public function resolve(Contributte\Translation\Translator $translator): ?string
	{
		if (!$this->session->isStarted() && $this->httpResponse->isSent()) {
			trigger_error('The advice of session locale resolver is required but the session has not been started and headers had been already sent. Either start your sessions earlier or disabled the SessionResolver.', E_USER_WARNING);
			return null;
		}

		if (!isset($this->sessionSection[self::$parameter])) {
			return null;
		}

		if (!in_array(Nette\Utils\Strings::substring($this->sessionSection[self::$parameter], 0, 2), array_map(function ($locale) {return Nette\Utils\Strings::substring($locale, 0, 2);}, $translator->availableLocales), true)) {
			return null;
		}

		return $this->sessionSection[self::$parameter];
	}
}
