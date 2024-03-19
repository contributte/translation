<?php declare(strict_types = 1);

namespace Contributte\Translation\Wrappers;

use Stringable;

class NotTranslate implements Stringable
{

	public string $message;

	public function __construct(
		string $message
	)
	{
		$this->message = $message;
	}


	public function __toString(): string
	{
		return $this->message;
	}

}
