<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation;

use Nette;


/**
 * @property     string $message
 * @property     array $parameters
 *
 * @author Ales Wita
 */
class Message
{
	use Nette\SmartObject;

	/** @var string */
	private $message;

	/** @var array */
	private $parameters;


	/**
	 * @param string $message
	 * @param mixed ...$parameters
	 */
	public function __construct(string $message, ...$parameters)
	{
		$this->message = $message;
		$this->parameters = $parameters;
	}


	/**
	 * @return string
	 */
	public function getMessage(): string
	{
		return $this->message;
	}


	/**
	 * @param string $string
	 * @return self
	 */
	public function setMessage(string $string): self
	{
		$this->message = $string;
		return $this;
	}


	/**
	 * @return array
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}


	/**
	 * @param array $array
	 * @return self
	 */
	public function setParameters(array $array): self
	{
		$this->parameters = $array;
		return $this;
	}
}
