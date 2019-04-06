<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation\Loaders;

use Nette;
use Nette\Schema\Expect;
use Symfony;
use Contributte;


/**
 * @author Ales Wita
 */
class Doctrine extends Symfony\Component\Translation\Loader\ArrayLoader implements Symfony\Component\Translation\Loader\LoaderInterface
{
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

		$schema = Expect::structure([
			'entity' => Expect::string($domain),
			'id' => Expect::string('id'),
			'locale' => Expect::string('locale'),
			'message' => Expect::string('message'),
			'timestamp' => Expect::string('timestamp'),
		]);

		$processor = new Nette\Schema\Processor;

		/** @var \stdClass $config */
		$config = $processor->process($schema, Nette\Neon\Neon::decode($content));

		$messages = [];

		/** @var \Doctrine\ORM\EntityRepository $repository */
		$repository = $this->em->getRepository($config->entity);

		foreach ($repository->findBy([$config->locale => $locale]) as $v1) {
			$id = $v1->{$config->id};
			$message = $v1->{$config->message};

			if (array_key_exists($id, $messages)) {
				throw new Contributte\Translation\InvalidStateException('Id "' . $id . '" declared twice in "' . $config->entity . '" entity/domain.');
			}

			$messages[$id] = $message;
		}

		$catalogue = parent::load($messages, $locale, $domain);
		$catalogue->addResource(new Contributte\Translation\Resources\Database($resource, $this->getTimestamp($resource, $locale, $config, $repository)));

		return $catalogue;
	}


	/**
	 * @param string $resource
	 * @param string $locale
	 * @param \stdClass $config
	 * @param \Doctrine\ORM\EntityRepository $repository
	 * @return int
	 */
	public function getTimestamp(string $resource, string $locale, \stdClass $config, \Doctrine\ORM\EntityRepository $repository): int
	{
		$resourceTimestamp = filemtime($resource);

		if ($resourceTimestamp === false) {
			$resourceTimestamp = 0;
		}

		$entityTimestamp = $repository->findOneBy([$config->locale => $locale], [$config->timestamp => 'DESC']);

		if ($entityTimestamp === null || $resourceTimestamp >= $entityTimestamp->{$config->timestamp}) {
			return $resourceTimestamp;
		}

		return $entityTimestamp->{$config->timestamp};
	}
}
