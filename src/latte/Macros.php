<?php

/**
 * This file is part of the Translette/Translation
 */

declare(strict_types=1);

namespace Translette\Translation\Latte;

use Latte;
use Translette;


/**
 * @author Ales Wita
 * @author Filip Prochazka
 */
class Macros extends Latte\Macros\MacroSet
{
	/**
	 * @param Latte\Compiler $compiler
	 */
	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);

		$me->addMacro('_', [$me, 'macroTranslate'], [$me, 'macroTranslate']);
		$me->addMacro('translator', [$me, 'macroPrefix'], [$me, 'macroPrefix']);
	}


	/**
	 * https://github.com/nette/latte/blob/master/src/Latte/Macros/CoreMacros.php#L205
	 *
	 * {_ ...}
	 */
	public function macroTranslate(Latte\MacroNode $node, Latte\PhpWriter $writer)
	{
		if ($node->closing) {
			if (strpos($node->content, '<?php') === false) {
				$value = var_export($node->content, true);
				$node->content = '';

			} else {
				$node->openingCode = '<?php ob_start(function () {}) ?>' . $node->openingCode;
				$value = 'ob_get_clean()';
			}

			return $writer->write('$_fi = new LR\FilterInfo(%var); echo %modifyContent($this->filters->filterContent("translate", $_fi, %raw))', $node->context[0], $value);

		} elseif ($node->empty = ($node->args !== '')) {
			// return $writer->write('echo %modify(($this->filters->translate)(%node.args))');

			if (Translette\Translation\Helpers::macroWithoutParameters($node)) {
				return $writer->write('echo %modify(call_user_func($this->filters->translate, %node.word))');
			}

			return $writer->write('echo %modify(call_user_func($this->filters->translate, %node.word, %node.args))');
		}
	}


	/**
	 * {translate ...}
	 *
	 * @throws Latte\CompileException
	 */
	public function macroPrefix(Latte\MacroNode $node, Latte\PhpWriter $writer)
	{
		if ($node->closing) {
			if ($node->content !== null && $node->content !== '') {
				return $writer->write('$this->global->translator->prefix = null;');
			}

		} else {
			if ($node->args === '') {
				throw new Latte\CompileException('Expected message prefix, none given.');
			}

			return $writer->write('$this->global->translator->prefix = %node.word;');
		}
	}
}
