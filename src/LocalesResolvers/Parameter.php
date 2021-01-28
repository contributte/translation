<?php declare(strict_types = 1);

namespace Contributte\Translation\LocalesResolvers;

use Contributte\Translation\Translator;
use Nette\Http\IRequest;

class Parameter implements ResolverInterface
{

	/** @var string */
	public static $parameter = 'locale';

	/** @var \Nette\Http\IRequest */
	private $request;

	public function __construct(
		IRequest $request
	)
	{
		$this->request = $request;
	}

	public function resolve(
		Translator $translator
	): ?string
	{
		return $this->request->getQuery(self::$parameter);
	}

}
