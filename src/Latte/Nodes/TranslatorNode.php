<?php declare(strict_types = 1);

namespace Contributte\Translation\Latte\Nodes;

use Contributte\Translation\Helpers;
use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

class TranslatorNode extends StatementNode
{

	public ExpressionNode $prefix;

	public AreaNode $content;

	public function print(
		PrintContext $context
	): string
	{
		$prefixProp = Helpers::createLatteProperty('Prefix');
		$tempPrefixProp = Helpers::createLatteProperty('TempPrefix');

		return $context->format(
			sprintf('
			%s = %s ?? [] %%line;
			array_push(%s, %s ?? null);
			%s = [%%node];

			%%node

			%s = array_pop(%s);
			', $tempPrefixProp, $tempPrefixProp, $tempPrefixProp, $prefixProp, $prefixProp, $prefixProp, $tempPrefixProp),
			$this->position,
			$this->prefix,
			$this->content
		);
	}

	public function &getIterator(): \Generator
	{
		yield $this->prefix;
		yield $this->content;
	}

	/** @return \Generator<int, ?array<mixed>, array{AreaNode, ?Tag}, TranslatorNode> */
	public static function create(
		Tag $tag
	): \Generator
	{
		$tag->expectArguments();
		$variable = $tag->parser->parseUnquotedStringOrExpression();

		$node = new TranslatorNode();
		$node->prefix = $variable;
		[$node->content] = yield;

		return $node;
	}

}
