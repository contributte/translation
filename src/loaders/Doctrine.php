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
class Doctrine extends Symfony\Component\Translation\Loader\ArrayLoader implements Symfony\Component\Translation\Loader\LoaderInterface
{
	/** @var array */
	public static $defaults = [
		'table' => 'messages',
		'id' => 'id',
		'locale' => 'locale',
		'message' => 'message',
	];

	/** @var \Doctrine\ORM\Decorator\EntityManagerDecorator $em */
	private $em;


	/**
	 * @param \Doctrine\ORM\Decorator\EntityManagerDecorator $em
	 */
	public function __construct(\Doctrine\ORM\Decorator\EntityManagerDecorator $em)
	{
		$this->em = $em;
	}


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

		$processor = new Nette\Schema\Processor;
		/** @var \stdClass $config */
		$config = $processor->process(self::getSchema(['table' => $domain]), Nette\Neon\Neon::decode($content));


		$messages = [];

		foreach ($this->em->getRepository($config->table)->findBy([$config->locale => $locale]) as $v1) {
			$id = $v1->{$config->id};
			$message = $v1->{$config->message};

			if (array_key_exists($id, $messages)) {
				throw new Contributte\Translation\InvalidStateException('Id "' . $id . '" declared twice in "' . $config->table . '" table/domain.');
			}

			$messages[$id] = $message;
		}

		$catalogue = parent::load($messages, $locale, $domain);
		$catalogue->addResource(new Symfony\Component\Config\Resource\FileResource($resource));

		return $catalogue;
	}


	/**
	 * @internal
	 *
	 * @param array $defaults
	 * @return Nette\Schema\Elements\Structure
	 */
	private static function getSchema(array $defaults = []): Nette\Schema\Elements\Structure
	{
		return Expect::structure([
			'table' => Expect::string(array_key_exists('table', $defaults) ? $defaults['table'] : self::$defaults['table']),
			'id' => Expect::string(array_key_exists('id', $defaults) ? $defaults['id'] : self::$defaults['id']),
			'locale' => Expect::string(array_key_exists('locale', $defaults) ? $defaults['locale'] : self::$defaults['locale']),
			'message' => Expect::string(array_key_exists('message', $defaults) ? $defaults['message'] : self::$defaults['message']),
		]);
	}
}
