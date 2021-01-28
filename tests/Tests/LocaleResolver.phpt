<?php declare(strict_types = 1);

namespace Tests;

use Contributte;
use Nette;
use Tester\Assert;
use Tests;

$container = require __DIR__ . '/../bootstrap.php';

class LocaleResolver extends Tests\TestAbstract
{

	public function test01(): void
	{
		$container = Helpers::createContainerFromConfigurator($this->container->getParameters()['tempDir'], [
			'translation' => [
				'locales' => [
					'default' => 'cs',
					'whitelist' => ['cs', 'en'],
				],
			],
		]);

		/** @var Contributte\Translation\Translator $translator */
		$translator = $container->getByType(Nette\Localization\ITranslator::class);

		/** @var Tests\LocaleResolverMock $mockResolver */
		$mockResolver = $container->getByType(LocaleResolverMock::class);

		$localeResolver = $translator->getLocaleResolver();

		Assert::count(1, $localeResolver->getResolvers());
		Assert::same('cs', $localeResolver->resolve($translator));
		$mockResolver->setLocale('en');
		Assert::same('en', $localeResolver->resolve($translator));
		$mockResolver->setLocale('sk');
		Assert::same('cs', $localeResolver->resolve($translator));
	}

}

(new LocaleResolver($container))->run();
