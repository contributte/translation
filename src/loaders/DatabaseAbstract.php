<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation\Loaders;

use Contributte;
use Nette;
use Nette\Schema\Expect;
use Symfony;


/**
 * @author Ales Wita
 */
abstract class DatabaseAbstract extends Symfony\Component\Translation\Loader\ArrayLoader implements Symfony\Component\Translation\Loader\LoaderInterface
{
	/** @var array */
	public static $defaults = [
		'table' => 'messages',
		'id' => 'id',
		'locale' => 'locale',
		'message' => 'message',
	];


	/**
	 * {@inheritdoc}
	 *
	 * @throws Contributte\Translation\InvalidArgumentException|Contributte\Translation\InvalidStateException
	 */
	public function load($resource, $locale, $domain = 'messages')
	{
		$content = @file_get_contents($resource); // @ -> prevent E_WARNING and thrown an exception

		if ($content === false) {
			throw new Contributte\Translation\InvalidArgumentException('Something wrong with resource file "' . $resource . '".');
		}

		$catalogue = parent::load(
			$this->getMessages(
				(new Nette\Schema\Processor)->process
				(
					$this->getSchema(['table' => $domain]),
					Nette\Neon\Neon::decode($content)
				),
				$resource,
				$locale,
				$domain
			),
			$locale,
			$domain
		);
		$catalogue->addResource(new Symfony\Component\Config\Resource\FileResource($resource));

		return $catalogue;
	}


	/**
	 * @internal
	 *
	 * @param array $defaults
	 * @return Nette\Schema\Elements\Structure
	 */
	private function getSchema(array $defaults = []): Nette\Schema\Elements\Structure
	{
		return Expect::structure([
			'table' => Expect::string(array_key_exists('table', $defaults) ? $defaults['table'] : self::$defaults['table']),
			'id' => Expect::string(array_key_exists('id', $defaults) ? $defaults['id'] : self::$defaults['id']),
			'locale' => Expect::string(array_key_exists('locale', $defaults) ? $defaults['locale'] : self::$defaults['locale']),
			'message' => Expect::string(array_key_exists('message', $defaults) ? $defaults['message'] : self::$defaults['message']),
		]);
	}


	/**
	 * @param \stdClass $config
	 * @param string $resource
	 * @param string $locale
	 * @param string $domain
	 * @return array
	 */
	abstract protected function getMessages(\stdClass $config, string $resource, string $locale, string $domain): array;
}
