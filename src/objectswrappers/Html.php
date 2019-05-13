<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation\ObjectsWrappers;

use Nette;


/**
 * @author Ales Wita
 */
class Html implements ObjectWrapperInterface
{
	/** @var Nette\Utils\Html */
	private $object;


	/**
	 * @param Nette\Utils\Html $object
	 */
	public function __construct(Nette\Utils\Html $object)
	{
		$this->object = $object;
	}


	/**
	 * @return string|null
	 */
	public function getMessage(): ?string
	{
		return $this->object->getText();
	}


	/**
	 * @param string $string
	 */
	public function setMessage(string $string): void
	{
		$this->object->setText($string);
	}
}
