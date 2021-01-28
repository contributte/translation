<?php declare(strict_types = 1);

namespace Tests\Latte;

use Contributte\Translation\Latte\Filters;
use Contributte\Translation\Translator;
use Latte\Runtime\FilterInfo;
use Mockery;
use Tester\Assert;
use Tests;

$container = require __DIR__ . '/../../bootstrap.php';

final class FiltersTest extends Tests\TestAbstract
{

	public function test01(): void
	{
		$translatorMock = Mockery::mock(Translator::class);

		$translatorMock->shouldReceive('translate')
			->once()
			->withArgs(['message', 'parameters'])
			->andReturn('');

		$filters = new Filters($translatorMock);
		Assert::same('', $filters->translate(new FilterInfo(), 'message', 'parameters'));
	}

}

(new FiltersTest($container))->run();
