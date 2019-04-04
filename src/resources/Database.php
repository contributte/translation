<?php

/**
 * This file is part of the Translette/Translation
 */

declare(strict_types=1);

namespace Translette\Translation\Resources;

use Symfony;


/**
 * @author Ales Wita
 */
class Database implements Symfony\Component\Config\Resource\SelfCheckingResourceInterface, \Serializable
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
	public function __toString(): string
	{
		return $this->resource;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isFresh($timestamp): bool
	{
		return $this->timestamp <= $timestamp;
	}


	/**
	 * @internal
	 *
	 * {@inheritdoc}
	 */
	public function serialize()
	{
		return serialize($this->resource);
	}


	/**
	 * @internal
	 *
	 * {@inheritdoc}
	 */
	public function unserialize($serialized)
	{
		$this->resource = unserialize($serialized);
	}


	/**
	 * @return string
	 */
	public function getResource(): string
	{
		return $this->resource;
	}
}
