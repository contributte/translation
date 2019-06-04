<?php declare(strict_types = 1);

/**
 * This file is part of the Contributte/Translation
 */

require __DIR__ . '/../vendor/autoload.php';
//require __DIR__ . '/../../vendor/autoload.php';

Tester\Environment::setup();

$configurator = new Nette\Configurator();

$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->addDirectory(__DIR__ . '/../src')
	->register();

return $configurator->createContainer();
