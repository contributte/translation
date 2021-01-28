<?php declare(strict_types = 1);

namespace Contributte\Translation\Wrappers;

class NotTranslate
{

	/** @var string */
	public $message;

	public function __construct(
		string $message
	)
	{
		$this->message = $message;
	}

}
