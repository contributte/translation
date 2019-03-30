<?php

/**
 * This file is part of the Translette/Translation
 */

declare(strict_types=1);

namespace Translette\Translation;

use Nette;
use Translette;


/**
 * @property-read Translette\Translation\Translator $translator
 * @property-read string $prefix
 *
 * @author Ales Wita
 * @author Filip Prochazka
 */
class PrefixedTranslator implements Nette\Localization\ITranslator
{
	use Nette\SmartObject;

	/** @var Translette\Translation\Translator */
	private $translator;

	/** @var string */
	private $prefix;


	/**
	 * @param Translette\Translation\Translator $translator
	 * @param string $prefix
	 */
	public function __construct(Translator $translator, string $prefix)
	{
		$this->translator = $translator;
		$this->prefix = $prefix;
	}


	/**
	 * @return Translette\Translation\Translator
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
	public function translate($message, ...$parameters): string
	{
		$this->translator->addPrefix($this->prefix);
		$message = $this->translator->translate($message, ...$parameters);
		$this->translator->removePrefix();
		return $message;
	}
}
