<?php declare(strict_types = 1);

namespace Contributte\Translation\Wrappers;

class NotTranslate
{

	public string $message;

	public function __construct(
		string $message
	)
	{
		$this->message = $message;
	}

}
