<?php

/**
 * This file is part of the Contributte/Translation
 */

declare(strict_types=1);

namespace Contributte\Translation;

use Contributte;
use Nette;


/**
 * @property-read Contributte\Translation\Translator $translator
 * @property-read string $prefix
 *
 * @author Ales Wita
 * @author Filip Prochazka
 */
class PrefixedTranslator implements Nette\Localization\ITranslator
{
	use Nette\SmartObject;

	/** @var Contributte\Translation\Translator */
	private $translator;

	/** @var string */
	private $prefix;


	/**
	 * @param Contributte\Translation\Translator $translator
	 * @param string $prefix
	 */
	public function __construct(Translator $translator, string $prefix)
	{
		$this->translator = $translator;
		$this->prefix = $prefix;
	}


	/**
	 * @return Contributte\Translation\Translator
	 */
	public function getTranslator(): Translator
	{
		return $this->translator;
	}


	/**
	 * @return string
	 */
	public function getPrefix(): string
	{
		return $this->prefix;
	}


	/**
	 * {@inheritdoc}
	 */
	public function translate($message, $count = null, $params = [], $domain = null, $locale = null)
	{
		$this->translator->addPrefix($this->prefix);
		$message = $this->translator->translate($message, $count, $params, $domain, $locale);
		$this->translator->removePrefix();
		return $message;
	}
}
