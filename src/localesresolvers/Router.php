<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation\LocalesResolvers;

use Nette;
use Contributte;


/**
 * @author Ales Wita
 */
class Router implements ResolverInterface
{
	use Nette\SmartObject;

	/** @var string */
	public static $parameter = 'locale';

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
	 * @param Contributte\Translation\Translator $translator
	 * @return string|null
	 */
	public function resolve(Contributte\Translation\Translator $translator): ?string
	{
		$match = $this->router->match($this->request);

		if ($match !== null && array_key_exists(self::$parameter, $match)) {
			return $match[self::$parameter];
		}

		return null;
	}
}
