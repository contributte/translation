<?php

/**
 * This file is part of the Translette\Translation
 */

declare(strict_types=1);

namespace Translette\Translation\Latte;

use Kdyby\Translation\PrefixedTranslator;
use Latte;


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
		$me->addMacro('translator', [$me, 'macroDomain'], [$me, 'macroDomain']);
	}


	/**
	 * {_$var|modifiers}
	 * {_$var, $count|modifiers}
	 * {_"Sample message", $count|modifiers}
	 * {_some.string.id, $count|modifiers}
	 *
	 * @param Latte\MacroNode $node
	 * @param Latte\PhpWriter $writer
	 * @return string
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

		} elseif ($node->args !== '') {
			$node->empty = true;

			if ($this->containsOnlyOneWord($node)) {
				return $writer->write('echo %modify(call_user_func($this->filters->translate, %node.word))');

			} else {
				return $writer->write('echo %modify(call_user_func($this->filters->translate, %node.word, %node.args))');
			}
		}
	}


	/**
	 * @param Latte\MacroNode $node
	 * @param Latte\PhpWriter $writer
	 * @return string|null
	 * @throws Latte\CompileException for invalid domain
	 */
	public function macroDomain(Latte\MacroNode $node, Latte\PhpWriter $writer)
	{
		if ($node->closing) {
			if ($node->content !== null && $node->content !== '') {
				return $writer->write('$_translator->unregister($this);');
			}

		} else {
			if ($node->empty) {
				throw new Latte\CompileException('Expected message prefix, none given');
			}

			return $writer->write('$_translator = ' . PrefixedTranslator::class . '::register($this, %node.word);');
		}
	}


	/**
	 * @param Latte\MacroNode $node
	 * @return bool
	 */
	private function containsOnlyOneWord(Latte\MacroNode $node)
	{
		$result = trim($node->tokenizer->joinUntil(',')) === trim($node->args);
		$node->tokenizer->reset();
		return $result;
	}
}
