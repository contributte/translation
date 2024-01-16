<?php declare(strict_types = 1);

namespace Contributte\Translation\Wrappers;

class Message
{

	public string $message;

	/** @var array<mixed> */
	public array $parameters;

	public function __construct(
		string $message,
		mixed ...$parameters
	)
	{
		$this->message = $message;
		$this->parameters = $parameters;
	}

}
