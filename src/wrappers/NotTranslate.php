<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation\Wrappers;

use Nette;


/**
 * @property     string $message
 *
 * @author Ales Wita
 */
class NotTranslate
{
	use Nette\SmartObject;

	/** @var string */
	private $message;


	/**
	 * @param string $message
	 */
	public function __construct(string $message)
	{
		$this->message = $message;
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
}
