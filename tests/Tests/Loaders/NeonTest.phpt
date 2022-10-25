<?php declare(strict_types = 1);

namespace Tests\Loaders;

use Contributte\Translation\Exceptions\InvalidArgument;
use Contributte\Translation\Loaders\Neon;
use Symfony\Component\Translation\MessageCatalogue;
use Tester\Assert;
use Tester\FileMock;
use Tests\TestAbstract;

$container = require __DIR__ . '/../../bootstrap.php';

final class NeonTest extends TestAbstract
{

	public function test01(): void
	{
		$file = FileMock::create('
test:
	for: "translate"', 'neon');

		$catalogue = (new Neon())->load($file, 'en');

		Assert::true($catalogue instanceof MessageCatalogue);
		Assert::same('en', $catalogue->getLocale());
		Assert::same(['messages'], $catalogue->getDomains());
		Assert::same('translate', $catalogue->get('test.for'));
		Assert::same('missing.translate', $catalogue->get('missing.translate'));
		Assert::same('missing.translate', $catalogue->get('missing.translate', 'domain'));
		Assert::same(['messages' => ['test.for' => 'translate']], $catalogue->all());

		$catalogue = (new Neon())->load($file, 'cs', 'domain');
		Assert::same('cs', $catalogue->getLocale());
		Assert::same(['domain'], $catalogue->getDomains());
		Assert::same('translate', $catalogue->get('test.for', 'domain'));
		Assert::same('missing.translate', $catalogue->get('missing.translate', 'domain'));
		Assert::same('missing.translate', $catalogue->get('missing.translate'));
		Assert::same(['domain' => ['test.for' => 'translate']], $catalogue->all());

		$catalogue = (new Neon())->load(FileMock::create('', 'neon'), 'en');
		Assert::same('en', $catalogue->getLocale());
		// Assert::same(['messages'], $catalogue->getDomains()); // 6.0
		// Assert::same(['messages' => []], $catalogue->all()); // 6.0
		// Assert::same([], $catalogue->getDomains()); // 6.1
		// Assert::same([], $catalogue->all()); // 6.1
	}

	public function test02(): void
	{
		Assert::exception(static function (): void {
			(new Neon())->load('unknown_file', 'en');
		}, InvalidArgument::class, 'Something wrong with resource file "unknown_file".');
	}

}

(new NeonTest($container))->run();
