<?php declare(strict_types = 1);

namespace Contributte\Translation\Loaders;

use Contributte\Translation\Exceptions\InvalidArgument;
use Nette\Neon\Neon;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Loader\LoaderInterface;

abstract class DatabaseAbstract extends ArrayLoader implements LoaderInterface
{

	/** @var array{table: string, id: string, locale: string, message: string} */
	public static array $defaults = [
		'table' => 'messages',
		'id' => 'id',
		'locale' => 'locale',
		'message' => 'message',
	];

	/**
	 * {@inheritdoc}
	 *
	 * @throws \Contributte\Translation\Exceptions\InvalidArgument
	 */
	public function load(
		$resource,
		string $locale,
		string $domain = 'messages'
	)
	{
		if (!\is_string($resource)) {
			throw new InvalidArgument('Parameter resource must be string.');
		}

		$content = @file_get_contents($resource); // @ -> prevent E_WARNING and thrown an exception

		if ($content === false) {
			throw new InvalidArgument('Something wrong with resource file "' . $resource . '".');
		}

		/** @var array<string, string> $settings */
		$settings = Neon::decode($content);

		$settings['table'] = $settings['table'] ?? $domain;

		$config = [
			'table' => $settings['table'],
			'id' => $settings['id'] ?? self::$defaults['id'],
			'locale' => $settings['locale'] ?? self::$defaults['locale'],
			'message' => $settings['message'] ?? self::$defaults['message'],
		];

		$catalogue = parent::load(
			$this->getMessages(
				$config,
				$resource,
				$locale,
				$domain
			),
			$locale,
			$domain
		);

		$catalogue->addResource(new FileResource($resource));

		return $catalogue;
	}

	/**
	 * @param array{table: string, id: string, locale: string, message: string} $config
	 * @return array<string>
	 */
	abstract protected function getMessages(
		array $config,
		string $resource,
		string $locale,
		string $domain
	): array;

}
