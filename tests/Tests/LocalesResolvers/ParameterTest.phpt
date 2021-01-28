<?php declare(strict_types = 1);

namespace Tests\LocalesResolvers;

use Contributte\Translation\LocalesResolvers\Parameter;
use Contributte\Translation\Translator;
use Mockery;
use Nette\Http\Request;
use Nette\Http\UrlScript;
use Tester\Assert;
use Tests\TestAbstract;

$container = require __DIR__ . '/../../bootstrap.php';

final class ParameterTest extends TestAbstract
{

	public function test01(): void
	{
		Assert::same('', $this->resolve(''));
		Assert::same('en', $this->resolve('en'));
		Assert::same('cs', $this->resolve('cs'));
	}

	public function test02(): void
	{
		$request = new Request(new UrlScript('https://www.example.com'));

		$resolver = new Parameter($request);
		$translatorMock = Mockery::mock(Translator::class);

		Assert::null($resolver->resolve($translatorMock));
	}

	private function resolve(
		?string $locale
	): ?string
	{
		$request = new Request(new UrlScript('https://www.example.com/?' . Parameter::$parameter . '=' . $locale));

		$resolver = new Parameter($request);
		$translatorMock = Mockery::mock(Translator::class);

		return $resolver->resolve($translatorMock);
	}

}

(new ParameterTest($container))->run();
