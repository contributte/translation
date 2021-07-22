<?php declare(strict_types = 1);

namespace Contributte\Translation\LocalesResolvers;

use Contributte\Translation\Translator;
use Nette\Http\IRequest;
use Nette\Routing\Router as NetteRouter;

class Router implements ResolverInterface
{

	public static string $parameter = 'locale';

	private IRequest $request;

	private NetteRouter $router;

	public function __construct(
		IRequest $request,
		NetteRouter $router
	)
	{
		$this->request = $request;
		$this->router = $router;
	}

	public function resolve(
		Translator $translator
	): ?string
	{
		$match = $this->router->match($this->request);

		if ($match !== null && array_key_exists(self::$parameter, $match)) {
			return $match[self::$parameter];
		}

		return null;
	}

}
