<?php declare(strict_types = 1);

namespace Contributte\Translation\LocalesResolvers;

use Contributte;
use Nette;

class Parameter implements ResolverInterface
{

	use Nette\SmartObject;

	/** @var string */
	public static $parameter = 'locale';

	/** @var Nette\Http\IRequest */
	private $request;

	public function __construct(Nette\Http\IRequest $request)
	{
		$this->request = $request;
	}

	public function resolve(Contributte\Translation\Translator $translator): ?string
	{
		return $this->request->getQuery(self::$parameter);
	}

}
