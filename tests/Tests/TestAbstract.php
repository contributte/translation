<?php declare(strict_types = 1);

namespace Tests;

use Nette\DI\Container;
use Tester\TestCase;

abstract class TestAbstract extends TestCase
{

	/** @var \Nette\DI\Container */
	protected $container;

	public function __construct(
		Container $container
	)
	{
		$this->container = $container;
	}

}
