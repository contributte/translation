<?php declare(strict_types = 1);

namespace Tests\DI;

use Contributte;
use Nette;
use Psr;
use stdClass;
use Symfony;
use Tester\Assert;
use Tests;
use UnexpectedValueException;

$container = require __DIR__ . '/../../bootstrap.php';

class TranslationExtension extends Tests\TestAbstract
{

	public function test01(): void
	{
		Assert::exception(function (): void {
			Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'locales' => [
						'whitelist' => ['en', 'en'],
					],
				],
			]);
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'Whitelist settings have not unique values.');
		Assert::exception(function (): void {
			Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'locales' => [
						'whitelist' => ['en'],
						'default' => 'cs',
					],
				],
			]);
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'If you set whitelist, default locale must be on him.');
		Assert::exception(function (): void {
			Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'localeResolvers' => [stdClass::class],
				],
			]);
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'Resolver must implement interface "' . Contributte\Translation\LocalesResolvers\ResolverInterface::class . '".');
		Assert::exception(function (): void {
			Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'cache' => [
						'factory' => stdClass::class,
					],
				],
			]);
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'Cache factory must implement interface "' . Symfony\Component\Config\ConfigCacheFactoryInterface::class . '".');
		Assert::exception(function (): void {
			Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'loaders' => [stdClass::class],
				],
			]);
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'Loader must implement interface "' . Symfony\Component\Translation\Loader\LoaderInterface::class . '".');
		Assert::exception(function (): void {
			Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'dirs' => [__DIR__ . '/__no_exists__'],
				],
			]);
		}, UnexpectedValueException::class);
		Assert::exception(function (): void {
			Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'logger' => true,
					'localeResolvers' => [],
				],
			]);
		}, Nette\DI\MissingServiceException::class, "Service of type '" . Psr\Log\LoggerInterface::class . "' not found.");
		Assert::exception(function (): void {
			Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'logger' => stdClass::class,
					'localeResolvers' => [],
				],
			]);
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'Logger must implement interface "' . Psr\Log\LoggerInterface::class . '".');
		Assert::exception(function (): void {
			Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'logger' => 1,
					'localeResolvers' => [],
				],
			]);
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'Option "logger" must be bool for autowired or class name as string.');
		Assert::exception(function (): void {
			Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
				'translation' => [
					'translatorFactory' => stdClass::class,
				],
			]);
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'Translator must extends class "' . Contributte\Translation\Translator::class . '".');
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
			Assert::true(Nette\Utils\Strings::contains($e->getMessage(), __DIR__ . '/__translation_provider_dir__'));// translation provider dirs first !!
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

		$tracyPanel = $translator->getTracyPanel();

		Assert::count(1, $tracyPanel->getResources());
		Assert::count(1, $panel->getResources());
		Assert::count(1, $tracyPanel->getIgnoredResources());
		Assert::count(1, $panel->getIgnoredResources());

		$foo = $tracyPanel->getIgnoredResources();
		$foo = end($foo);
		Assert::same('messages', end($foo));
		Assert::true(Nette\Utils\Strings::contains(key($foo), 'messages.cs_CZ.neon'));

		$foo = $panel->getIgnoredResources();
		$foo = end($foo);
		Assert::same('messages', end($foo));
		Assert::true(Nette\Utils\Strings::contains(key($foo), 'messages.cs_CZ.neon'));

		$symfonyTranslator = $container->getByType(Symfony\Contracts\Translation\TranslatorInterface::class);
		Assert::same($translator, $symfonyTranslator);

		$contributteTranslator = $container->getByType(Contributte\Translation\Translator::class);
		Assert::same($translator, $contributteTranslator);
	}

	public function test04(): void
	{
		$container = Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
			'translation' => [
				'logger' => Tests\PsrLoggerMock::class,
			],
		]);

		Assert::count(1, $container->findByType(Tests\PsrLoggerMock::class));
	}

	public function test05(): void
	{
		$container = Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
			'translation' => [
				'locales' => [
					'fallback' => ['cs_CZ'],
				],
			],
		]);

		/** @var Contributte\Translation\Translator $translator */
		$translator = $container->getByType(Nette\Localization\ITranslator::class);

		Assert::same($translator->getFallbackLocales(), ['cs_CZ']);
	}

	public function test06(): void
	{
		$container = Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
			'translation' => [
				'locales' => ['whitelist' => ['en']],
				'translatorFactory' => Tests\CustomTranslatorMock::class,
			],
		]);

		/** @var Contributte\Translation\Translator $translator */
		$translator = $container->getByType(Nette\Localization\ITranslator::class);

		Assert::type(Tests\CustomTranslatorMock::class, $translator);

		$factoryTranslator = $container->getByType(Tests\CustomTranslatorMock::class);
		Assert::same($translator, $factoryTranslator);
	}

	public function test07(): void
	{
		$container = Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
			'translation' => [
				'returnOriginalMessage' => false,
			],
		]);

		/** @var Contributte\Translation\Translator $translator */
		$translator = $container->getByType(Nette\Localization\ITranslator::class);

		Assert::false($translator->returnOriginalMessage);
	}

	public function test08(): void
	{
		$container = Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
			'translation' => [
				'returnOriginalMessage' => false,
			],
		]);

		/** @var Contributte\Translation\Translator $translator */
		$translator = $container->getByType(Nette\Localization\ITranslator::class);

		Assert::false($translator->returnOriginalMessage);
	}

	public function test09(): void
	{
		$container = Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
			'translation' => [
				'autowired' => false,
			],
		]);

		$translator = $container->getByType(Nette\Localization\ITranslator::class, false);

		Assert::null($translator);

		$container = Tests\Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
			'translation' => [
				'autowired' => [
					Contributte\Translation\Translator::class,
				],
			],
		]);

		$translator = $container->getByType(Nette\Localization\ITranslator::class, false);

		Assert::null($translator);

		$translator = $container->getByType(Contributte\Translation\Translator::class, false);

		Assert::true($translator instanceof Contributte\Translation\Translator);
	}

}

(new TranslationExtension($container))->run();
