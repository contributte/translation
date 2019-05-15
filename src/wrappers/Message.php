<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation\Wrappers;

use Nette;


/**
 * @property     string $message
 * @property     int|array|null $count
 * @property     string|array|null $params
 * @property     string|null $domain
 * @property     string|null $locale
 *
 * @author Ales Wita
 */
class Message
{
	use Nette\SmartObject;

	/** @var string */
	private $message;

	/** @var int|array|null */
	private $count;

	/** @var string|array|null */
	private $params;

	/** @var string|null */
	private $domain;

	/** @var string|null */
	private $locale;


	/**
	 * @param string $message
	 * @param int|array|null $count
	 * @param string|array|null $params
	 * @param string|null $domain
	 * @param string|null $locale
	 */
	public function __construct(string $message, $count = null, $params = [], $domain = null, $locale = null)
	{
		$this->message = $message;
		$this->count = $count;
		$this->params = $params;
		$this->domain = $domain;
		$this->locale = $locale;
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
	 * @return int|array|null
	 */
	public function getCount()
	{
		return $this->count;
	}


	/**
	 * @param int|array|null $mixed
	 * @return self
	 */
	public function setCount($mixed): self
	{
		$this->count = $mixed;
		return $this;
	}


	/**
	 * @return string|array|null
	 */
	public function getParams()
	{
		return $this->params;
	}


	/**
	 * @param string|array|null $mixed
	 * @return self
	 */
	public function setParams($mixed): self
	{
		$this->params = $mixed;
		return $this;
	}


	/**
	 * @return string|null
	 */
	public function getDomain(): ?string
	{
		return $this->domain;
	}


	/**
	 * @param string|null $string
	 * @return self
	 */
	public function setDomain(?string $string): self
	{
		$this->domain = $string;
		return $this;
	}


	/**
	 * @return string|null
	 */
	public function getLocale(): ?string
	{
		return $this->locale;
	}


	/**
	 * @param string|null $string
	 * @return self
	 */
	public function setLocale(?string $string): self
	{
		$this->locale = $string;
		return $this;
	}
}
