<?php

/**
 * This file is part of the Translette/Translation
 */

declare(strict_types=1);

namespace Translette\Translation\Resources;

use Translette;
use Symfony;


/**
 * @author Ales Wita
 */
class Database implements Symfony\Component\Config\Resource\SelfCheckingResourceInterface
{
	/** @var string */
	private $resource;

	/** @var int */
	private $timestamp;


	/**
	 * @param string $resource
	 * @param int $timestamp
	 */
	public function __construct(string $resource, int $timestamp)
	{
		$this->resource = $resource;
		$this->timestamp = $timestamp;
	}


	/**
	 * {@inheritdoc}
	 */
	public function __toString()
	{
		return $this->resource;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isFresh($timestamp)
	{
		return $this->timestamp <= $timestamp;
	}
}
