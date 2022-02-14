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

final class TranslationExtensionTest2 extends TestAbstract
{

	public function test01(): void
	{
		$tempDir = Helpers::generateRandomTempDir($this->container->getParameters()['tempDir'] . '/' . self::class);

		Assert::exception(
			function () use ($tempDir): void {
				Helpers::createContainerFromConfigurator(
					$tempDir,
					[
						'translation' => [
							'locales' => [
								'whitelist' => ['en', 'en'],
							],
						],
					]
				);
			},
			InvalidArgument::class,
			'Whitelist settings have not unique values.'
		);

		Helpers::clearTempDir($tempDir);

		Assert::exception(
			function () use ($tempDir): void {
				Helpers::createContainerFromConfigurator(
					$tempDir,
					[
						'translation' => [
							'locales' => [
								'whitelist' => ['en'],
								'default' => 'cs',
							],
						],
					]
				);
			},
			InvalidArgument::class,
			'If you set whitelist, default locale must be on him.'
		);

		Helpers::clearTempDir($tempDir);

		Assert::exception(
			function () use ($tempDir): void {
				Helpers::createContainerFromConfigurator(
					$tempDir,
					[
						'translation' => [
							'localeResolvers' => [stdClass::class],
						],
					]
				);
			},
			InvalidArgument::class,
			'Resolver must implement interface "' . ResolverInterface::class . '".'
		);

		Helpers::clearTempDir($tempDir);

		Assert::exception(
			function () use ($tempDir): void {
				Helpers::createContainerFromConfigurator(
					$tempDir,
					[
						'translation' => [
							'cache' => [
								'factory' => stdClass::class,
							],
						],
					]
				);
			},
			InvalidArgument::class,
			'Cache factory must implement interface "' . ConfigCacheFactoryInterface::class . '".'
		);

		Helpers::clearTempDir($tempDir);

		Assert::exception(
			function () use ($tempDir): void {
				Helpers::createContainerFromConfigurator(
					$tempDir,
					[
					'translation' => [
						'loaders' => [stdClass::class],
					],
					]
				);
			},
			InvalidArgument::class,
			'Loader must implement interface "' . LoaderInterface::class . '".'
		);

		Helpers::clearTempDir($tempDir);

		Assert::exception(
			function () use ($tempDir): void {
				Helpers::createContainerFromConfigurator(
					$tempDir,
					[
						'translation' => [
							'dirs' => [__DIR__ . '/__no_exists__'],
						],
					]
				);
			},
			UnexpectedValueException::class
		);

		Helpers::clearTempDir($tempDir);

		Assert::exception(
			function () use ($tempDir): void {
				Helpers::createContainerFromConfigurator(
					$tempDir,
					[
						'translation' => [
							'logger' => true,
							'localeResolvers' => [],
						],
					]
				);
			},
			MissingServiceException::class
		);

		Helpers::clearTempDir($tempDir);

		Assert::exception(
			function () use ($tempDir): void {
				Helpers::createContainerFromConfigurator(
					$tempDir,
					[
						'translation' => [
							'logger' => stdClass::class,
							'localeResolvers' => [],
						],
					]
				);
			},
			InvalidArgument::class,
			'Logger must implement interface "' . LoggerInterface::class . '".'
		);

		Helpers::clearTempDir($tempDir);

		Assert::exception(
			function () use ($tempDir): void {
				Helpers::createContainerFromConfigurator(
					$tempDir,
					[
					'translation' => [
						'logger' => 1,
						'localeResolvers' => [],
					],
					]
				);
			},
			InvalidArgument::class,
			'Option "logger" must be bool for autowired or class name as string.'
		);

		Helpers::clearTempDir($tempDir);

		Assert::exception(
			function () use ($tempDir): void {
				Helpers::createContainerFromConfigurator(
					$tempDir,
					[
						'translation' => [
							'translatorFactory' => stdClass::class,
						],
					]
				);
			},
			InvalidArgument::class,
			'Translator must extends class "' . Translator::class . '".'
		);

		Helpers::clearTempDir($tempDir);
	}

}

(new TranslationExtensionTest2($container))->run();
