<?php declare(strict_types = 1);

namespace Tests\DI;

use Contributte;
use Nette;
use Psr;
use stdClass;
use Symfony;
use Tester;
use Tests;
use UnexpectedValueException;

$container = require __DIR__ . '/../../bootstrap.php';

class TranslationExtension extends Tests\TestAbstract
{

	public function test01(): void
	{
		Tester\Assert::exception(function (): void {
			Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'locales' => [
						'whitelist' => ['en', 'en'],
					],
				],
			]);
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'Whitelist settings have not unique values.');
		Tester\Assert::exception(function (): void {
			Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'locales' => [
						'whitelist' => ['en'],
						'default' => 'cs',
					],
				],
			]);
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'If you set whitelist, default locale must be on him.');
		Tester\Assert::exception(function (): void {
			Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'localeResolvers' => [stdClass::class],
				],
			]);
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'Resolver must implement interface "' . Contributte\Translation\LocalesResolvers\ResolverInterface::class . '".');
		Tester\Assert::exception(function (): void {
			Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'cache' => [
						'factory' => stdClass::class,
					],
				],
			]);
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'Cache factory must implement interface "' . Symfony\Component\Config\ConfigCacheFactoryInterface::class . '".');
		Tester\Assert::exception(function (): void {
			Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'loaders' => [stdClass::class],
				],
			]);
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'Loader must implement interface "' . Symfony\Component\Translation\Loader\LoaderInterface::class . '".');
		Tester\Assert::exception(function (): void {
			Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'dirs' => [__DIR__ . '/__no_exists__'],
				],
			]);
		}, UnexpectedValueException::class);
		Tester\Assert::exception(function (): void {
			Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'logger' => true,
					'localeResolvers' => [],
				],
			]);
		}, Nette\DI\MissingServiceException::class, "Service of type '" . Psr\Log\LoggerInterface::class . "' not found.");
		Tester\Assert::exception(function (): void {
			Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'logger' => stdClass::class,
					'localeResolvers' => [],
				],
			]);
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'Logger must implement interface "' . Psr\Log\LoggerInterface::class . '".');
		Tester\Assert::exception(function (): void {
			Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'logger' => 1,
					'localeResolvers' => [],
				],
			]);
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'Option "logger" must be bool for autowired or class name as string.');
	}

	public function test02(): void
	{
		try {
			$loader = new Nette\DI\ContainerLoader($this->container->getParameters()['tempDir'], true);

			$loader->load(function (Nette\DI\Compiler $compiler): void {
				$compiler->addExtension('translation', new Contributte\Translation\DI\TranslationExtension());
				$compiler->addExtension('translationProvider', new class extends Nette\DI\CompilerExtension implements Contributte\Translation\DI\TranslationProviderInterface {

					/**
					 * @return string[]
					 */
					public function getTranslationResources(): array
					{
						return [__DIR__ . '/__translation_provider_dir__'];
					}

				});
				$compiler->addConfig(['parameters' => $this->container->getParameters(), 'translation' => ['dirs' => [__DIR__ . '__config_dir__']]]);
			});

		} catch (UnexpectedValueException $e) {
			Tester\Assert::true(Nette\Utils\Strings::contains($e->getMessage(), __DIR__ . '/__translation_provider_dir__'));// translation provider dirs first !!
		}
	}

	public function test03(): void
	{
		$container = Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
			'translation' => [
				'locales' => ['whitelist' => ['en']],
			],
		]);

		/** @var Contributte\Translation\Tracy\Panel $panel */
		$panel = $container->getByType(Contributte\Translation\Tracy\Panel::class);

		/** @var Contributte\Translation\Translator $translator */
		$translator = $container->getByType(Nette\Localization\ITranslator::class);

		Tester\Assert::count(1, $translator->tracyPanel->getResources());
		Tester\Assert::count(1, $panel->getResources());
		Tester\Assert::count(1, $translator->tracyPanel->getIgnoredResources());
		Tester\Assert::count(1, $panel->getIgnoredResources());

		$foo = $translator->tracyPanel->getIgnoredResources();
		$foo = end($foo);
		Tester\Assert::same('messages', end($foo));
		Tester\Assert::true(Nette\Utils\Strings::contains(key($foo), 'messages.cs_CZ.neon'));

		$foo = $panel->getIgnoredResources();
		$foo = end($foo);
		Tester\Assert::same('messages', end($foo));
		Tester\Assert::true(Nette\Utils\Strings::contains(key($foo), 'messages.cs_CZ.neon'));

		$symfonyTranslator = $container->getByType(Symfony\Contracts\Translation\TranslatorInterface::class);
		Tester\Assert::same($translator, $symfonyTranslator);
	}

	public function test04(): void
	{
		$container = Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
			'translation' => [
				'logger' => Tests\PsrLoggerMock::class,
			],
		]);

		Tester\Assert::count(1, $container->findByType(Tests\PsrLoggerMock::class));
	}

}

(new TranslationExtension($container))->run();
