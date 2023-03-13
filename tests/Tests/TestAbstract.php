<?php declare(strict_types = 1);

namespace Tests;

use Nette\DI\Container;
use Tester\TestCase;

abstract class TestAbstract extends TestCase
{

	protected Container $container;

	protected bool $isNewNetteUtils;

	public function __construct(
		Container $container
	)
	{
		if (class_exists('\Composer\InstalledVersions')) { // Composer 2
			$netteUtilsVersion = \Composer\InstalledVersions::getPrettyVersion('nette/utils');
		} else { // Composer 1
			$composerRaw = \Nette\Utils\FileSystem::read(__DIR__ . '/../../composer.lock');
			$composerData = \Nette\Utils\Json::decode($composerRaw);
			$netteUtilsVersion = '0.0.0';
			foreach ($composerData->packages as $package) {
				if ($package->name !== 'nette/utils') {
					continue;
				}

				$netteUtilsVersion = ltrim($package->version, 'v');
			}
		}

		$this->container = $container;
		$this->isNewNetteUtils = version_compare($netteUtilsVersion, '4.0.0', '>=');
	}

}
