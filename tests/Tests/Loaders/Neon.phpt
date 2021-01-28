<?php declare(strict_types = 1);

namespace Tests\Loaders;

use Contributte;
use Symfony;
use Tester\Assert;
use Tester\FileMock;
use Tests;

$container = require __DIR__ . '/../../bootstrap.php';

class Neon extends Tests\TestAbstract
{

	public function test01(): void
	{
		$file = FileMock::create('
test:
	for: "translate"');

		$catalogue = (new Contributte\Translation\Loaders\Neon())->load($file, 'en');

		Assert::true($catalogue instanceof Symfony\Component\Translation\MessageCatalogue);
		Assert::same('en', $catalogue->getLocale());
		Assert::same(['messages'], $catalogue->getDomains());
		Assert::same('translate', $catalogue->get('test.for'));
		Assert::same('missing.translate', $catalogue->get('missing.translate'));
		Assert::same('missing.translate', $catalogue->get('missing.translate', 'domain'));
		Assert::same(['messages' => ['test.for' => 'translate']], $catalogue->all());

		$catalogue = (new Contributte\Translation\Loaders\Neon())->load($file, 'cs', 'domain');
		Assert::same('cs', $catalogue->getLocale());
		Assert::same(['domain'], $catalogue->getDomains());
		Assert::same('translate', $catalogue->get('test.for', 'domain'));
		Assert::same('missing.translate', $catalogue->get('missing.translate', 'domain'));
		Assert::same('missing.translate', $catalogue->get('missing.translate'));
		Assert::same(['domain' => ['test.for' => 'translate']], $catalogue->all());

		$catalogue = (new Contributte\Translation\Loaders\Neon())->load(FileMock::create(''), 'en');
		Assert::same('en', $catalogue->getLocale());
		Assert::same(['messages'], $catalogue->getDomains());
		Assert::same(['messages' => []], $catalogue->all());
	}

	public function test02(): void
	{
		Assert::exception(function (): void {
			(new Contributte\Translation\Loaders\Neon())->load('unknown_file', 'en');
		}, Contributte\Translation\Exceptions\InvalidArgument::class, 'Something wrong with resource file "unknown_file".');
	}

}

(new Neon($container))->run();
