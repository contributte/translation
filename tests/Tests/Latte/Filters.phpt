<?php declare(strict_types = 1);

namespace Tests\Latte;

use Contributte;
use Latte;
use Mockery;
use Tester\Assert;
use Tests;

$container = require __DIR__ . '/../../bootstrap.php';

class Filters extends Tests\TestAbstract
{

	public function test01(): void
	{
		$translatorMock = Mockery::mock(Contributte\Translation\Translator::class);

		$translatorMock->shouldReceive('translate')
			->once()
			->withArgs(['message', 'parameters'])
			->andReturn('');

		$filters = new Contributte\Translation\Latte\Filters($translatorMock);
		Assert::same('', $filters->translate(new Latte\Runtime\FilterInfo(), 'message', 'parameters'));
	}

}

(new Filters($container))->run();
