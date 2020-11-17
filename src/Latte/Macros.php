<?php declare(strict_types = 1);

namespace Contributte\Translation\Latte;

use Contributte;
use Latte;

class Macros extends Latte\Macros\MacroSet
{

	final public function __construct(Latte\Compiler $compiler)
	{
		parent::__construct($compiler);
	}

	public static function install(Latte\Compiler $compiler): void
	{
		$me = new static($compiler);

		$me->addMacro('_', [$me, 'macroTranslate'], [$me, 'macroTranslate']);
		$me->addMacro('translator', [$me, 'macroPrefix'], [$me, 'macroPrefix']);
	}

	/**
	 * {_ ...}
	 *
	 * @throws Latte\CompileException
	 */
	public function macroTranslate(Latte\MacroNode $node, Latte\PhpWriter $writer): string
	{
		if ($node->closing) {
			if (strpos($node->content, '<?php') === false) {
				$value = var_export($node->content, true);
				$node->content = '';

			} else {
				$node->openingCode = '<?php ob_start(function () {}) ?>' . $node->openingCode;
				$value = 'ob_get_clean()';
			}

			if (!defined(Latte\Engine::class . '::VERSION_ID') || Latte\Engine::VERSION_ID < 20900) {
				return $writer->write('$_fi = new LR\FilterInfo(%var); echo %modifyContent($this->filters->filterContent("translate", $_fi, %raw))', $node->context[0], $value);
			}

			return $writer->write('$__fi = new LR\FilterInfo(%var); echo %modifyContent($this->filters->filterContent("translate", $__fi, %raw))', $node->context[0], $value);

		} elseif ($node->empty = ($node->args !== '')) {
			if (Contributte\Translation\Helpers::macroWithoutParameters($node)) {
				return $writer->write('echo %modify(call_user_func($this->filters->translate, %node.word))');
			}

			return $writer->write('echo %modify(call_user_func($this->filters->translate, %node.word, %node.args))');
		}

		return '';
	}

	/**
	 * {translate ...}
	 *
	 * @throws Latte\CompileException
	 */
	public function macroPrefix(Latte\MacroNode $node, Latte\PhpWriter $writer): string
	{
		if ($node->closing) {
			if ($node->content !== null && $node->content !== '') {
				return $writer->write('$this->global->translator->prefix = $this->global->translator->prefixTemp;');
			}

			return '';
		}

		if ($node->args === '') {
			throw new Latte\CompileException('Expected message prefix, none given.');
		}

		return $writer->write('$this->global->translator->prefix = [%node.word];');
	}

}
