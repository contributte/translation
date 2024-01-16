<?php declare(strict_types = 1);

namespace Contributte\Translation\Latte\Nodes;

use Latte\Compiler\NodeHelpers;
use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\NopNode;
use Latte\Compiler\Nodes\Php;
use Latte\Compiler\Nodes\Php\ModifierNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\Nodes\TextNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

class TranslateNode extends StatementNode
{

	public AreaNode $content;

	public ModifierNode $modifier;

	public function print(
		PrintContext $context
	): string
	{
		return $this->content instanceof TextNode ? $context->format(
			'
					$ʟ_fi = new LR\FilterInfo(%dump);
					echo %modifyContent(%dump) %line;
				',
			$context->getEscaper()->export(),
			$this->modifier,
			$this->content->content,
			$this->position,
		) : $context->format(
			'
					ob_start(fn() => ""); try {
						%node
					} finally {
						$ʟ_tmp = ob_get_clean();
					}
					$ʟ_fi = new LR\FilterInfo(%dump);
					echo %modifyContent($ʟ_tmp) %line;
				',
			$this->content,
			$context->getEscaper()->export(),
			$this->modifier,
			$this->position,
		);
	}

	public function &getIterator(): \Generator
	{
		yield $this->content;
		yield $this->modifier;
	}

	/** @return \Generator<int, ?array<mixed>, array{AreaNode, ?Tag}, TranslateNode|NopNode> */
	public static function create(
		Tag $tag
	): \Generator
	{
		$tag->outputMode = $tag::OutputKeepIndentation;

		$node = new TranslateNode();
		$args = $tag->parser->parseArguments();
		$node->modifier = $tag->parser->parseModifier();
		$node->modifier->escape = true;
		if ($tag->void) {
			return new NopNode();
		}

		[$node->content] = yield;

		if (($text = NodeHelpers::toText($node->content)) !== null) {
			$node->content = new TextNode($text);
		}

		array_unshift($node->modifier->filters, new Php\FilterNode(new Php\IdentifierNode('translate'), $args->toArguments()));

		return $node;
	}

}
