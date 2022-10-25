<?php declare(strict_types = 1);

namespace Contributte\Translation\DI;

use Contributte\Translation\Exceptions\InvalidState;
use Nette\DI\Definitions\Definition;
use Nette\DI\Definitions\Reference;
use Nette\DI\Definitions\Statement;

class Helpers
{

	/**
	 * @param Statement|class-string $input
	 * @return class-string
	 */
	public static function unwrapEntity(Statement|string $input): string
	{
		if ($input instanceof Statement) {
			/** @var class-string|Reference|Definition|mixed[] $entity */
			$entity = $input->getEntity();
			if (is_string($entity)) {
				return $entity;
			}

			throw new InvalidState('Only string statements allowed');
		}

		return $input;
	}

}
