<?php declare(strict_types = 1);

namespace Tests\Toolkit;

use Doctrine\ORM\Configuration;
use Nette\DI\Compiler;
use Nette\DI\Config\Adapters\NeonAdapter;
use Nette\Neon\Neon;

final class Helpers
{

	/**
	 * @return mixed[]
	 */
	public static function neon(string $str): array
	{
		return (new NeonAdapter())->process((array) Neon::decode($str));
	}

	public static function createConfiguration(?callable $callback = null): Configuration
	{
		$container = Container::of()
			->withDefaults()
			->withCompiler(
				static function (
					Compiler $compiler
				) use (
					$callback
				): void {
					if ($callback === null) {
						return;
					}

					$callback($compiler);
				}
			)
			->build();

		return $container->getByType(Configuration::class);
	}

}
