<?php declare(strict_types = 1);

namespace Tests\LocalesResolvers;

use Contributte\Translation\FallbackResolver;
use Contributte\Translation\LocaleResolver;
use Contributte\Translation\LocalesResolvers\Session;
use Contributte\Translation\Translator;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Http\Session as NetteSession;
use Nette\Http\UrlScript;
use Tester\Assert;
use Tests\TestAbstract;

$container = require __DIR__ . '/../../bootstrap.php';

final class SessionTest extends TestAbstract
{

	public function test01(): void
	{
		Assert::null($this->resolve(null));
		Assert::same('cs', $this->resolve('cs'));
		Assert::same('en', $this->resolve('en'));
		Assert::null($this->resolve(null));
	}

	private function resolve(
		?string $locale
	): ?string
	{
		$response = new Response();

		$request = new Request(
			new UrlScript('https://example.com')
		);

		$session = new NetteSession(
			$request,
			$response
		);

		$resolver = new Session(
			$response,
			$session
		);

		$translator = new Translator(
			new LocaleResolver($this->container),
			new FallbackResolver(),
			'en'
		);

		$resolver->setLocale($locale);

		return $resolver->resolve($translator);
	}

}

(new SessionTest($container))->run();
