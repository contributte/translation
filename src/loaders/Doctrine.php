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
class Doctrine extends Symfony\Component\Translation\Loader\ArrayLoader implements DbInterface
{
	/** @var string */
	public static $columnId = 'id';

	/** @var string */
	public static $columnLocale = 'locale';

	/** @var string */
	public static $columnMessage = 'message';

	/** @var string */
	public static $columnTimestamp = 'timestamp';

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
	 */
	public function load($resource, $locale, $domain = 'messages')
	{
		$content = @file_get_contents($resource); // @ -> prevent E_WARNING and thrown an exception

		if ($content === false) {
			throw new Translette\Translation\InvalidArgumentException('Something wrong with resource file "' . $resource . '".');
		}

		$configuration = Nette\Neon\Neon::decode($content);

		if (!array_key_exists('entity', $configuration)) {
			$configuration['entity'] = $domain;
		}

		if (!array_key_exists('id', $configuration)) {
			$configuration['id'] = self::$columnId;
		}

		if (!array_key_exists('locale', $configuration)) {
			$configuration['locale'] = self::$columnLocale;
		}

		if (!array_key_exists('message', $configuration)) {
			$configuration['message'] = self::$columnMessage;
		}

		if (!array_key_exists('timestamp', $configuration)) {
			$configuration['timestamp'] = self::$columnTimestamp;
		}

		$messages = [];
		$repository = $this->em->getRepository($domain);

		foreach ($repository->findBy([$configuration['locale'] => $locale]) as $v1) {
			$messages[$v1->{$configuration['id']}] = $v1->{$configuration['message']};
		}

		$catalogue = parent::load($messages, $locale, $domain);
		$catalogue->addResource(new Symfony\Component\Config\Resource\FileResource($resource)); // @todo

		return $catalogue;
	}


	/**
	 * {@inheritdoc}
	 *
	 * @todo
	 */
	public function getUpdateTimestamp(string $locale): int
	{
		return 0;
	}
}
