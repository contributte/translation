<?php declare(strict_types = 1);

namespace Contributte\Translation\LocalesResolvers;

use Contributte\Translation\Translator;
use Nette\Http\IResponse;
use Nette\Http\Session as NetteSession;

class Session implements ResolverInterface
{

	/** @var string|null */
	public static $parameter = 'locale';

	/** @var \Nette\Http\IResponse */
	private $httpResponse;

	/** @var \Nette\Http\Session */
	private $session;

	/** @var \Nette\Http\SessionSection<string, mixed> */
	private $sessionSection;

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

		if (!isset($this->sessionSection[self::$parameter])) {
			return null;
		}

		return $this->sessionSection[self::$parameter];
	}

}
