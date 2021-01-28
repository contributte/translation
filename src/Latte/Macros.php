<?php declare(strict_types = 1);

namespace Contributte\Translation\Latte;

use Contributte\Translation\Helpers;
use Latte\CompileException;
use Latte\Compiler;
use Latte\Engine;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;

class Macros extends MacroSet
{

	final public function __construct(
		Compiler $compiler
	)
	{
		parent::__construct($compiler);
	}

	public static function install(
		Compiler $compiler
	): void
	{
		$me = new static($compiler);

		$me->addMacro('_', [$me, 'macroTranslate'], [$me, 'macroTranslate']);
		$me->addMacro('translator', [$me, 'macroPrefix'], [$me, 'macroPrefix']);
	}

	/**
	 * {_ ...}
	 *
	 * @throws \Latte\CompileException
	 */
	public function macroTranslate(
		MacroNode $node,
		PhpWriter $writer
	): string
	{
		if ($node->closing) {
			if (strpos($node->content, '<?php') === false) {
				$value = var_export($node->content, true);
				$node->content = '';

			} else {
				$node->openingCode = '<?php ob_start(function () {}) ?>' . $node->openingCode;
				$value = 'ob_get_clean()';
			}

			if (!defined(Engine::class . '::VERSION_ID') || Engine::VERSION_ID < 20900) {
				return $writer->write('$_fi = new LR\FilterInfo(%var); echo %modifyContent($this->filters->filterContent("translate", $_fi, %raw))', $node->context[0], $value);
			}

			if (Engine::VERSION_ID >= 20900 && Engine::VERSION_ID < 20902) {
				return $writer->write('$__fi = new LR\FilterInfo(%var); echo %modifyContent($this->filters->filterContent("translate", $__fi, %raw))', $node->context[0], $value);
			}

			return $writer->write('$ʟ_fi = new LR\FilterInfo(%var); echo %modifyContent($this->filters->filterContent("translate", $ʟ_fi, %raw))', $node->context[0], $value);
		}

		if ($node->empty = ($node->args !== '')) {
			if (Helpers::macroWithoutParameters($node)) {
				return $writer->write('echo %modify(call_user_func($this->filters->translate, %node.word))');
			}

			return $writer->write('echo %modify(call_user_func($this->filters->translate, %node.word, %node.args))');
		}

		return '';
	}

	/**
	 * {translate ...}
	 *
	 * @throws \Latte\CompileException
	 */
	public function macroPrefix(
		MacroNode $node,
		PhpWriter $writer
	): string
	{
		if ($node->closing) {
			if ($node->content !== null && $node->content !== '') {
				return $writer->write('$this->global->translator->setPrefix($this->global->translator->getPrefixTemp());');
			}

			return '';
		}

		if ($node->args === '') {
			throw new CompileException('Expected message prefix, none given.');
		}

		return $writer->write('$this->global->translator->setPrefix([%node.word]);');
	}

}
