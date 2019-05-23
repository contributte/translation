<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Tests;

use Nette;
use Tester;

abstract class TestAbstract extends Tester\TestCase
{

	/** @var Nette\DI\Container */
	protected $container;

	public function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
	}

}
