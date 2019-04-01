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

	/** @var Nette\Http\IRequest */
	private $request;

	/** @var Nette\Routing\Router */
	private $router;


	/**
	 * @param Nette\Http\IRequest $request
	 * @param Nette\Routing\Router $router
	 */
	public function __construct(Nette\Http\IRequest $request, Nette\Routing\Router $router)
	{
		$this->request = $request;
		$this->router = $router;
	}


	/**
	 * @param Translette\Translation\Translator $translator
	 * @return string|null
	 */
	public function resolve(Translette\Translation\Translator $translator): ?string
	{
		$match = $this->router->match($this->request);

		if ($match !== null && array_key_exists(self::$localeParameter, $match)) {
			return $match[self::$localeParameter];
		}

		$locale = $this->request->getQuery(self::$localeParameter);

		if ($locale !== '') {
			return $locale;
		}

		return null;
	}
}
