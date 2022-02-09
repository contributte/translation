<?php declare(strict_types = 1);

namespace Contributte\Translation\Loaders;

use Contributte\Translation\Exceptions\InvalidArgument;
use Nette\Neon\Neon;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;
use Nette\Schema\Processor;
use stdClass;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Loader\LoaderInterface;

abstract class DatabaseAbstract extends ArrayLoader implements LoaderInterface
{

	/** @var array<string> */
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
		$locale,
		$domain = 'messages'
	)
	{
		$content = @file_get_contents($resource); // @ -> prevent E_WARNING and thrown an exception

		if ($content === false) {
			throw new InvalidArgument('Something wrong with resource file "' . $resource . '".');
		}

		$catalogue = parent::load(
			$this->getMessages(
				(new Processor())->process(
					$this->getSchema(['table' => $domain]),
					Neon::decode($content)
				),
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
	 * @param array<string> $defaults
	 */
	private function getSchema(
		array $defaults = []
	): Structure
	{
		return Expect::structure([
			'table' => Expect::string(array_key_exists('table', $defaults) ? $defaults['table'] : self::$defaults['table']),
			'id' => Expect::string(array_key_exists('id', $defaults) ? $defaults['id'] : self::$defaults['id']),
			'locale' => Expect::string(array_key_exists('locale', $defaults) ? $defaults['locale'] : self::$defaults['locale']),
			'message' => Expect::string(array_key_exists('message', $defaults) ? $defaults['message'] : self::$defaults['message']),
		]);
	}

	/**
	 * @return array<string>
	 */
	abstract protected function getMessages(
		stdClass $config,
		string $resource,
		string $locale,
		string $domain
	): array;

}
