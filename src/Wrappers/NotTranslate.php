<?php declare(strict_types = 1);

namespace Contributte\Translation\Wrappers;

use Nette;

/**
 * @property     string $message
 */
class NotTranslate
{

	use Nette\SmartObject;

	/** @var string */
	private $message;

	public function __construct(string $message)
	{
		$this->message = $message;
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

}
