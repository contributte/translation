<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Contributte\Translation\Wrappers;

use Nette;

/**
 * @property     string $message
 * @property     mixed[] $parameters
 */
class Message
{

	use Nette\SmartObject;

	/** @var string */
	private $message;

	/** @var mixed[] */
	private $parameters;

	/**
	 * @param mixed[] ...$parameters
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
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

	/**
	 * @return mixed[]
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	/**
	 * @param mixed[] $array
	 */
	public function setParameters(array $array): self
	{
		$this->parameters = $array;
		return $this;
	}

}
