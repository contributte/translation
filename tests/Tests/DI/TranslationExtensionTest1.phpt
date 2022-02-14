<?php declare(strict_types = 1);

namespace Tests\DI;

use Contributte\Translation\Exceptions\InvalidArgument;
use Contributte\Translation\LocalesResolvers\ResolverInterface;
use Contributte\Translation\Translator;
use Nette\DI\MissingServiceException;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Tester\Assert;
use Tests\Helpers;
use Tests\TestAbstract;
use UnexpectedValueException;

$container = require __DIR__ . '/../../bootstrap.php';

final class TranslationExtensionTest1 extends TestAbstract
{

	public function test01(): void
	{
		Assert::exception(function (): void {
			Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'locales' => [
						'whitelist' => ['en', 'en'],
					],
				],
			]);
		}, InvalidArgument::class, 'Whitelist settings have not unique values.');
		Assert::exception(function (): void {
			Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'locales' => [
						'whitelist' => ['en'],
						'default' => 'cs',
					],
				],
			]);
		}, InvalidArgument::class, 'If you set whitelist, default locale must be on him.');
		Assert::exception(function (): void {
			Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'localeResolvers' => [stdClass::class],
				],
			]);
		}, InvalidArgument::class, 'Resolver must implement interface "' . ResolverInterface::class . '".');
		Assert::exception(function (): void {
			Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'cache' => [
						'factory' => stdClass::class,
					],
				],
			]);
		}, InvalidArgument::class, 'Cache factory must implement interface "' . ConfigCacheFactoryInterface::class . '".');
		Assert::exception(function (): void {
			Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'loaders' => [stdClass::class],
				],
			]);
		}, InvalidArgument::class, 'Loader must implement interface "' . LoaderInterface::class . '".');
		Assert::exception(function (): void {
			Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'dirs' => [__DIR__ . '/__no_exists__'],
				],
			]);
		}, UnexpectedValueException::class);
		Assert::exception(function (): void {
			Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'logger' => true,
					'localeResolvers' => [],
				],
			]);
		}, MissingServiceException::class);
		Assert::exception(function (): void {
			Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'logger' => stdClass::class,
					'localeResolvers' => [],
				],
			]);
		}, InvalidArgument::class, 'Logger must implement interface "' . LoggerInterface::class . '".');
		Assert::exception(function (): void {
			Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'logger' => 1,
					'localeResolvers' => [],
				],
			]);
		}, InvalidArgument::class, 'Option "logger" must be bool for autowired or class name as string.');
		Assert::exception(function (): void {
			Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'translatorFactory' => stdClass::class,
				],
			]);
		}, InvalidArgument::class, 'Translator must extends class "' . Translator::class . '".');
	}

}

(new TranslationExtensionTest1($container))->run();
