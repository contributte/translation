<?php declare(strict_types = 1);

namespace Tests\Fixtures;

use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

class DummyLoader extends ArrayLoader implements LoaderInterface
{

	public function load(mixed $resource, string $locale, string $domain = 'messages'): MessageCatalogue
	{
		return parent::load([], $locale, $domain);
	}

}
