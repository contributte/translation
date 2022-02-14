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

(new TranslationExtensionTest2($container))->run();
