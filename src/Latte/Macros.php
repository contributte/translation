<?php declare(strict_types = 1);

namespace Contributte\Translation\Latte;

use Contributte\Translation\Helpers;
use Latte\CompileException;
use Latte\Compiler;
use Latte\Engine;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;
use Nette\Utils\Strings;

class Macros extends MacroSet
{

	final public function __construct(
		Compiler $compiler
	)
	{
		parent::__construct($compiler);
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

			/** @phpstan-ignore-next-line */
			if (!defined(Engine::class . '::VERSION_ID') || Engine::VERSION_ID < 20900) {
				$latteProp = '$_fi';
			/** @phpstan-ignore-next-line */
			} elseif (Engine::VERSION_ID >= 20900 && Engine::VERSION_ID < 20902) {
				$latteProp = '$__fi';
			} else {
				$latteProp = '$ʟ_fi';
			}

			return $writer->write(sprintf('%s = new LR\FilterInfo(%%var); echo %%modifyContent($this->filters->filterContent("translate", %s, %%raw))', $latteProp, $latteProp), $node->context[0], $value);
		}

		if ($node->empty = ($node->args !== '')) {
			$messageProp = Helpers::createLatteProperty('Message');
			$prefixProp = Helpers::createLatteProperty('Prefix');

			$macroCodeEcho = self::macroWithoutParameters($node)
				? sprintf('echo %%modify(call_user_func($this->filters->translate, %s))', $messageProp)
				: sprintf('echo %%modify(call_user_func($this->filters->translate, %s, %%node.args))', $messageProp);

			return $writer->write(sprintf('
				%s = %%node.word;

				if (is_string(%s)) {
					%s = isset(%s) && !\Contributte\Translation\Helpers::isAbsoluteMessage(%%node.word) ? implode(".", %s) . "." . %%node.word : %%node.word;
				}

				%s;
			', $messageProp, $messageProp, $messageProp, $prefixProp, $prefixProp, $macroCodeEcho));
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
				return $writer->write(sprintf('%s = array_pop(%s);', $prefixProp, $tempPrefixProp));
			}

			return '';
		}

		if ($node->args === '') {
			throw new CompileException('Expected message prefix, none given.');
		}

		return $writer->write(sprintf('
			if (!isset(%s)) {
				%s = [];
			}

			if (isset(%s)) {
				%s[] = %s;
			}

			%s = [%%node.word];
		', $tempPrefixProp, $tempPrefixProp, $prefixProp, $tempPrefixProp, $prefixProp, $prefixProp));
	}

	public static function install(
		Compiler $compiler
	): void
	{
		$me = new static($compiler);

		$me->addMacro('_', [$me, 'macroTranslate'], [$me, 'macroTranslate']);
		$me->addMacro('translator', [$me, 'macroPrefix'], [$me, 'macroPrefix']);
	}

	public static function macroWithoutParameters(
		MacroNode $node
	): bool
	{
		$result = Strings::trim($node->tokenizer->joinUntil(',')) === Strings::trim($node->args);
		$node->tokenizer->reset();

		return $result;
	}

}
