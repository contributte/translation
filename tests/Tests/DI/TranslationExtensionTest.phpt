<?php declare(strict_types = 1);

namespace Tests\DI;

use Contributte\Translation\DI\TranslationExtension;
use Contributte\Translation\DI\TranslationProviderInterface;
use Contributte\Translation\Exceptions\InvalidArgument;
use Contributte\Translation\LocalesResolvers\ResolverInterface;
use Contributte\Translation\Tracy\Panel;
use Contributte\Translation\Translator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerLoader;
use Nette\DI\MissingServiceException;
use Nette\Localization\ITranslator;
use Nette\Utils\Strings;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tester\Assert;
use Tests\CustomTranslatorMock;
use Tests\Helpers;
use Tests\PsrLoggerMock;
use Tests\TestAbstract;
use UnexpectedValueException;

$container = require __DIR__ . '/../../bootstrap.php';

final class TranslationExtensionTest extends TestAbstract
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

	public function test02(): void
	{
		try {
			$loader = new ContainerLoader($this->container->getParameters()['tempDir'], true);

			$loader->load(function (Compiler $compiler): void {
				$compiler->addExtension('translation', new TranslationExtension());
				$compiler->addExtension('translationProvider', new class extends CompilerExtension implements TranslationProviderInterface {

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
			Assert::true(Strings::contains($e->getMessage(), __DIR__ . '/__translation_provider_dir__'));// translation provider dirs first !!
		}
	}

	public function test03(): void
	{
		$container = Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
			'translation' => [
				'locales' => ['whitelist' => ['en']],
			],
		]);

		/** @var Panel $panel */
		$panel = $container->getByType(Panel::class);

		/** @var Translator $translator */
		$translator = $container->getByType(ITranslator::class);

		$tracyPanel = $translator->getTracyPanel();

		Assert::count(1, $tracyPanel->getResources());
		Assert::count(1, $panel->getResources());
		Assert::count(1, $tracyPanel->getIgnoredResources());
		Assert::count(1, $panel->getIgnoredResources());

		$symfonyTranslator = $container->getByType(TranslatorInterface::class);
		Assert::same($translator, $symfonyTranslator);

		$contributteTranslator = $container->getByType(Translator::class);
		Assert::same($translator, $contributteTranslator);
	}

	public function test04(): void
	{
		$container = Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
			'translation' => [
				'logger' => PsrLoggerMock::class,
			],
		]);

		Assert::count(1, $container->findByType(PsrLoggerMock::class));
	}

	public function test05(): void
	{
		$container = Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
			'translation' => [
				'locales' => [
					'fallback' => ['cs_CZ'],
				],
			],
		]);

		/** @var Translator $translator */
		$translator = $container->getByType(ITranslator::class);

		Assert::same($translator->getFallbackLocales(), ['cs_CZ']);
	}

	public function test06(): void
	{
		$container = Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
			'translation' => [
				'locales' => ['whitelist' => ['en']],
				'translatorFactory' => CustomTranslatorMock::class,
			],
		]);

		/** @var \Contributte\Translation\Translator $translator */
		$translator = $container->getByType(ITranslator::class);

		Assert::type(CustomTranslatorMock::class, $translator);

		$factoryTranslator = $container->getByType(CustomTranslatorMock::class);
		Assert::same($translator, $factoryTranslator);
	}

	public function test07(): void
	{
		$container = Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
			'translation' => [
				'returnOriginalMessage' => false,
			],
		]);

		/** @var \Contributte\Translation\Translator $translator */
		$translator = $container->getByType(ITranslator::class);

		Assert::false($translator->returnOriginalMessage);
	}

	public function test08(): void
	{
		$container = Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
			'translation' => [
				'returnOriginalMessage' => false,
			],
		]);

		/** @var \Contributte\Translation\Translator $translator */
		$translator = $container->getByType(ITranslator::class);

		Assert::false($translator->returnOriginalMessage);
	}

	public function test09(): void
	{
		Assert::exception(
			function (): void {
				Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
					'translation' => [
						'autowired' => false,
					],
				]);
			},
			MissingServiceException::class
		);
	}

}

(new TranslationExtensionTest($container))->run();
