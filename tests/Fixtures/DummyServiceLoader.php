<?php declare(strict_types = 1);

namespace Tests\Fixtures;

class DummyServiceLoader extends DummyLoader
{

	public function __construct(public DummyService $service)
	{
	}

}
