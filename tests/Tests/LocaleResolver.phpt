<?php declare(strict_types = 1);

namespace Tests;

use Contributte;
use Nette;
use Tester;
use Tests;

$container = require __DIR__ . '/../bootstrap.php';

class LocaleResolver extends Tests\TestAbstract
{

	public function test01(): void
	{
		$container = $this->createContainer();

		/** @var Contributte\Translation\Translator $translator */
		$translator = $container->getByType(Nette\Localization\ITranslator::class);

		/** @var Tests\LocaleResolverMock $mockResolver */
		$mockResolver = $container->getByType(LocaleResolverMock::class);

		$localeResolver = $translator->getLocaleResolver();

		Tester\Assert::count(1, $localeResolver->getResolvers());
		Tester\Assert::same('cs', $localeResolver->resolve($translator));
		$mockResolver->setLocale('en');
		Tester\Assert::same('en', $localeResolver->resolve($translator));
	}

	/**
	 * @internal
	 */
	private function createContainer(): Nette\DI\Container
	{
		$configurator = new Nette\Configurator();

		$configurator->setTempDirectory($this->container->getParameters()['tempDir'])
			->addConfig([
				'extensions' => [
					'translation' => Contributte\Translation\DI\TranslationExtension::class,
				],
				'translation' => [
					'debug' => true,
					'locales' => [
						'default' => 'cs',
						'whitelist' => ['cs', 'en'],
					],
					'localeResolvers' => [
						LocaleResolverMock::class,
					],
					'dirs' => [
						__DIR__ . '/../lang',
					],
				],
			]);

		return $configurator->createContainer();
	}

}

(new LocaleResolver($container))->run();
