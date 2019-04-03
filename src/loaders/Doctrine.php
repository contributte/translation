<?php

/**
 * This file is part of the Translette/Translation
 */

declare(strict_types=1);

namespace Translette\Translation\Loaders;

use Nette;
use Symfony;
use Translette;


/**
 * @author Ales Wita
 */
class Doctrine extends Symfony\Component\Translation\Loader\ArrayLoader implements DatabaseInterface
{
	/** @var array */
	public $defaults = [
		'entity' => null,
		'id' => 'id',
		'locale' => 'locale',
		'message' => 'message',
		'timestamp' => 'timestamp',
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
	 * @throws Translette\Translation\InvalidArgumentException|Translette\Translation\InvalidStateException
	 */
	public function load($resource, $locale, $domain = 'messages')
	{
		$content = @file_get_contents($resource); // @ -> prevent E_WARNING and thrown an exception

		if ($content === false) {
			throw new Translette\Translation\InvalidArgumentException('Something wrong with resource file "' . $resource . '".');
		}

		$config = Nette\DI\Config\Helpers::merge($this->defaults, Nette\Neon\Neon::decode($content));

		if ($config['entity'] === null) {
			$config['entity'] = $domain;
		}

		$messages = [];
		$repository = $this->em->getRepository($config['entity']);

		foreach ($repository->findBy([$config['locale'] => $locale]) as $v1) {
			$id = $v1->{$config['id']};
			$message = $v1->{$config['message']};

			if (array_key_exists($id, $messages)) {
				throw new Translette\Translation\InvalidStateException('Id "' . $id . '" declared twice in "' . $config['entity'] . '" entity/domain.');
			}

			$messages[$id] = $message;
		}

		$catalogue = parent::load($messages, $locale, $config['entity']);
		$catalogue->addResource(new Translette\Translation\Resources\Database($resource, $this->getTimestamp($locale, $config, $repository)));

		return $catalogue;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getTimestamp(...$parameters): int
	{
		$locale = $parameters[0];
		$config = $parameters[1];
		$repository = $parameters[2];

		$timestamp = $repository->findOneBy([$config['locale'] => $locale], [$config['timestamp'] => 'DESC']);

		if ($timestamp !== null) {
			return $timestamp->{$config['timestamp']};
		}

		return 0;
	}
}
