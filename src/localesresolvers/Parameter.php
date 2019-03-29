<?php

/**
 * This file is part of the Translette/Translation
 */

declare(strict_types=1);

namespace Translette\Translation\LocalesResolvers;

use Nette;
use Translette;


/**
 * @author Ales Wita
 * @author Filip Prochazka
 */
class Parameter implements ResolverInterface
{
	use Nette\SmartObject;

	/** @var string */
	public static $localeParameter = 'locale';

	/** @var Nette\Application\Request */
	private $request;


	/**
	 * @param Nette\Application\Application $application
	 */
	public function __construct(Nette\Application\Application $application)
	{
		$requests = $application->getRequests();
		$request = end($requests);

		if ($request !== false) {
			$this->request = $request;
		}
	}


	/**
	 * @param Translette\Translation\Translator $translator
	 * @return string|null
	 */
	public function resolve(Translette\Translation\Translator $translator): ?string
	{
		if ($this->request === null) {
			return null;
		}

		$params = $this->request->getParameters();
		return array_key_exists(self::$localeParameter, $params) ? $params[self::$localeParameter] : null;
	}
}
