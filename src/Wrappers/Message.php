<?php declare(strict_types = 1);

namespace Contributte\Translation\Wrappers;

class Message
{

	/** @var string */
	private $message;

	/** @var array<mixed> */
	private $parameters;

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

	public function getMessage(): string
	{
		return $this->message;
	}

	public function setMessage(
		string $string
	): self
	{
		$this->message = $string;
		return $this;
	}

	/**
	 * @return array<mixed>
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	/**
	 * @param array<mixed> $array
	 */
	public function setParameters(
		array $array
	): self
	{
		$this->parameters = $array;
		return $this;
	}

}
