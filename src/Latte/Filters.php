<?php declare(strict_types = 1);

namespace Contributte\Translation\Latte;

use Latte\Runtime\FilterInfo;
use Nette\Localization\ITranslator;

class Filters
{

	/** @var \Nette\Localization\ITranslator */
	private $translator;

	public function __construct(
		ITranslator $translator
	)
	{
		$this->translator = $translator;
	}

	/**
	 * @param \Latte\Runtime\FilterInfo $filterInfo
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.UselessParameterAnnotation
	 * @param mixed $message
	 * @param mixed ...$parameters
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function translate(
		FilterInfo $filterInfo,
		$message,
		...$parameters
	): string
	{
		return $this->translator->translate($message, ...$parameters);
	}

}
