<?php declare(strict_types = 1);

namespace Contributte\Translation;

use Nette\Localization\ITranslator;

class PrefixedTranslator implements ITranslator
{

	/** @var \Contributte\Translation\Translator */
	private $translator;

	/** @var string */
	private $prefix;

	public function __construct(
		Translator $translator,
		string $prefix
	)
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
	 * @param mixed $message
	 * @param mixed ...$parameters
	 */
	public function translate(
		$message,
		...$parameters
	): string
	{
		$this->translator->addPrefix($this->prefix);
		$message = $this->translator->translate($message, ...$parameters);
		$this->translator->removePrefix();
		return $message;
	}

}
