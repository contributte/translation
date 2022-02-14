<?php declare(strict_types = 1);

namespace Tests\DI;

use Contributte\Translation\DI\TranslationExtension;
use Contributte\Translation\DI\TranslationProviderInterface;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerLoader;
use Nette\Utils\Strings;
use Tester\Assert;
use Tests\TestAbstract;
use UnexpectedValueException;

$container = require __DIR__ . '/../../bootstrap.php';

final class TranslationExtensionTest2 extends TestAbstract
{

	public function test01(): void
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

}

(new TranslationExtensionTest2($container))->run();
