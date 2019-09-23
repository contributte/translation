<?php declare(strict_types = 1);

namespace Contributte\Translation\Latte;

use Latte;
use Nette;

class Filters
{

	/** @var Nette\Localization\ITranslator */
	private $translator;

	public function __construct(Nette\Localization\ITranslator $translator)
	{
		$this->translator = $translator;
	}

	/**
	 * @param mixed $message
	 * @param mixed[] ...$args
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function translate(Latte\Runtime\FilterInfo $filterInfo, $message, ...$args): string
	{
		return $this->translator->translate($message, ...$args);
	}

}
