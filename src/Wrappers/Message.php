<?php declare(strict_types = 1);

namespace Contributte\Translation\Wrappers;

use Stringable;

class Message implements Stringable
{

	public string $message;

	/** @var array<mixed> */
	public array $parameters;

	/**
	 * @param array<mixed> ...$parameters
	 */
	public function __construct(
		string $message,
		...$parameters
	)
	{
		$this->message = $message;
		$this->parameters = $parameters;
	}


	public function __toString(): string
	{
		return $this->message;
	}

}
