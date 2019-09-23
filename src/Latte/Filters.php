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
	 * @param Latte\Runtime\FilterInfo $filterInfo
	 * @param mixed $message
	 * @param mixed ...$parameters
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function translate(Latte\Runtime\FilterInfo $filterInfo, $message, ...$parameters): string
	{
		return $this->translator->translate($message, ...$parameters);
	}

}
