<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Contributte\Translation\Wrappers;

use Nette;

/**
 * @property     string $message
 * @property     array $parameters
 */
class Message
{

	use Nette\SmartObject;

	/** @var string */
	private $message;

	/** @var array */
	private $parameters;

	public function __construct(string $message, ...$parameters)
	{
		$this->message = $message;
		$this->parameters = $parameters;
	}

	public function getMessage(): string
	{
		return $this->message;
	}

	public function setMessage(string $string): self
	{
		$this->message = $string;
		return $this;
	}

	public function getParameters(): array
	{
		return $this->parameters;
	}

	public function setParameters(array $array): self
	{
		$this->parameters = $array;
		return $this;
	}

}
