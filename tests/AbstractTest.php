<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation\Tests;

use Nette;
use Tester;


/**
 * @author Ales Wita
 */
abstract class AbstractTest extends Tester\TestCase
{
	/** @var Nette\DI\Container */
	protected $container;


	/**
	 * @param Nette\DI\Container $container
	 */
	public function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
	}
}
