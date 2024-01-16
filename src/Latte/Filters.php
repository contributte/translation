<?php declare(strict_types = 1);

namespace Contributte\Translation\Latte;

use Latte\Runtime\FilterInfo;
use Nette\Localization\ITranslator;

class Filters
{

	private ITranslator $translator;

	public function __construct(
		ITranslator $translator
	)
	{
		$this->translator = $translator;
	}

	public function translate(
		FilterInfo $filterInfo,
		mixed $message,
		mixed ...$parameters
	): string
	{
		return $this->translator->translate($message, ...$parameters);
	}

}
