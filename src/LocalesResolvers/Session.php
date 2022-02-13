<?php declare(strict_types = 1);

namespace Contributte\Translation\LocalesResolvers;

use Contributte\Translation\Translator;
use Nette\Http\IResponse;
use Nette\Http\Session as NetteSession;
use Nette\Http\SessionSection;

class Session implements ResolverInterface
{

	public static ?string $parameter = 'locale';

	private IResponse $httpResponse;

	private NetteSession $session;

	private SessionSection $sessionSection;

	public function __construct(
		IResponse $httpResponse,
		NetteSession $session
	)
	{
		$this->httpResponse = $httpResponse;
		$this->session = $session;
		$this->sessionSection = $session->getSection(self::class);
	}

	public function setLocale(
		?string $locale = null
	): self
	{
		$this->sessionSection[self::$parameter] = $locale;
		return $this;
	}

	public function resolve(
		Translator $translator
	): ?string
	{
		if (!$this->session->isStarted() && $this->httpResponse->isSent()) {
			trigger_error('The advice of session locale resolver is required but the session has not been started and headers had been already sent. Either start your sessions earlier or disable the SessionResolver.', E_USER_WARNING);
			return null;
		}

		$locale = $this->sessionSection[self::$parameter] ?? null;

		if (is_string($locale)) {
			return $locale;
		}

		return null;
	}

}
