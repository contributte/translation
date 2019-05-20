<?php declare(strict_types=1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Contributte\Translation\LocalesResolvers;

use Contributte;
use Nette;

class Router implements ResolverInterface
{

	use Nette\SmartObject;

	/** @var string */
	public static $parameter = 'locale';

	/** @var Nette\Http\IRequest */
	private $request;

	/** @var Nette\Routing\Router */
	private $router;

	public function __construct(Nette\Http\IRequest $request, Nette\Routing\Router $router)
	{
		$this->request = $request;
		$this->router = $router;
	}

	public function resolve(Contributte\Translation\Translator $translator): ?string
	{
		$match = $this->router->match($this->request);

		if ($match !== null && array_key_exists(self::$parameter, $match)) {
			return $match[self::$parameter];
		}

		return null;
	}

}
