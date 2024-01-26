<?php declare(strict_types = 1);

namespace Tests;

use Composer\InstalledVersions;
use Nette\DI\Container;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
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
			$netteUtilsVersion = InstalledVersions::getPrettyVersion('nette/utils');
		} else { // Composer 1
			$composerRaw = FileSystem::read(__DIR__ . '/../../composer.lock');

			/** @var array{ packages: array<array{ name: string, version: string }> } $composerData */
			$composerData = Json::decode($composerRaw, 1);

			$netteUtilsVersion = null;

			foreach ($composerData['packages'] as $package) {
				if ($package['name'] !== 'nette/utils') {
					continue;
				}

				$netteUtilsVersion = ltrim($package['version'], 'v');
			}
		}

		$this->container = $container;
		$this->isNewNetteUtils = version_compare($netteUtilsVersion ?? '0.0.0', '4.0.0', '>=');
	}

}
