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
				$latteProp = '$_fi';
			} elseif (Engine::VERSION_ID >= 20900 && Engine::VERSION_ID < 20902) {
				$latteProp = '$__fi';
			} else {
				$latteProp = '$ʟ_fi';
			}

			return $writer->write("$latteProp = new LR\\FilterInfo(%var); echo %modifyContent(\$this->filters->filterContent('translate', $latteProp, %raw))", $node->context[0], $value);
		}

		if ($node->empty = ($node->args !== '')) {
			$messageProp = Helpers::createLatteProperty('Message');
			$prefixProp = Helpers::createLatteProperty('Prefix');

			$macroCodeEcho1 = Helpers::macroWithoutParameters($node)
				? "echo %modify(call_user_func(\$this->filters->translate, $messageProp . %node.word))"
				: "echo %modify(call_user_func(\$this->filters->translate, $messageProp . %node.word, %node.args))";

			$macroCodeEcho2 = Helpers::macroWithoutParameters($node)
				? "echo %modify(call_user_func(\$this->filters->translate, %node.word))"
				: "echo %modify(call_user_func(\$this->filters->translate, %node.word, %node.args))";

			$macroCode = "
				if (is_string(%node.word)) {
					$messageProp = isset($prefixProp) && !\Contributte\Translation\Helpers::isAbsoluteMessage(%node.word) ? implode('.', $prefixProp) . '.' : '';
					$macroCodeEcho1;
				} else {
					$macroCodeEcho2;
				}
			";

			return $writer->write($macroCode);
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
		$prefixProp = Helpers::createLatteProperty('Prefix');
		$tempPrefixProp = Helpers::createLatteProperty('TempPrefix');

		if ($node->closing) {
			if ($node->content !== null && $node->content !== '') {
				return $writer->write("$prefixProp = array_pop($tempPrefixProp);");
			}

			return '';
		}

		if ($node->args === '') {
			throw new CompileException('Expected message prefix, none given.');
		}

		return $writer->write("
			if (!isset($tempPrefixProp)) {
				$tempPrefixProp = [];
			}

			if (isset($prefixProp)) {
				{$tempPrefixProp}[] = $prefixProp;
			}

			$prefixProp = [%node.word];
		");
	}

}
