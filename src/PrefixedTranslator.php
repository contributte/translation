<?php declare(strict_types=1);

/**
 * This file is part of the Contributte/Translation
 */

namespace Contributte\Translation;

use Contributte;
use Nette;

/**
 * @property-read Contributte\Translation\Translator $translator
 * @property-read string $prefix
 */
class PrefixedTranslator implements Nette\Localization\ITranslator
{

	use Nette\SmartObject;

	/** @var Contributte\Translation\Translator */
	private $translator;

	/** @var string */
	private $prefix;

	public function __construct(Translator $translator, string $prefix)
	{
		$this->translator = $translator;
		$this->prefix = $prefix;
	}

	public function getTranslator(): Translator
	{
		return $this->translator;
	}

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
