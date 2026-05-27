<?php declare(strict_types = 1);

namespace Contributte\Translation;

use Nette\Localization\ITranslator;

class PrefixedTranslator implements ITranslator
{

	private Translator $translator;

	private string $prefix;

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

	public function translate(
		mixed $message,
		mixed ...$parameters
	): string
	{
		$this->translator->addPrefix($this->prefix);
		$message = $this->translator->translate($message, ...$parameters);
		$this->translator->removePrefix();

		return $message;
	}

}
