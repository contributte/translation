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
	}


	/**
	 * https://github.com/nette/latte/blob/master/src/Latte/Macros/CoreMacros.php#L205
	 *
	 * @param Latte\MacroNode $node
	 * @param Latte\PhpWriter $writer
	 * @return string|null
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
				return $writer->write(' echo %modify(call_user_func($this->filters->translate, %node.word))');
			}

			return $writer->write('echo %modify(call_user_func($this->filters->translate, %node.word, %node.args))');
		}
	}
}
