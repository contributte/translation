<?php declare(strict_types = 1);

namespace Contributte\Translation\Latte;

use Contributte\Translation\Helpers;
use Contributte\Translation\Latte\Nodes\TranslateNode;
use Contributte\Translation\Latte\Nodes\TranslatorNode;
use Latte\Compiler\Node;
use Latte\Compiler\Nodes\Php\ArgumentNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\Expression\BinaryOpNode;
use Latte\Compiler\Nodes\Php\Expression\StaticCallNode;
use Latte\Compiler\Nodes\Php\Expression\VariableNode;
use Latte\Compiler\Nodes\Php\FilterNode;
use Latte\Compiler\Nodes\Php\IdentifierNode;
use Latte\Compiler\Nodes\Php\NameNode;
use Latte\Compiler\Nodes\Php\Scalar\NullNode;
use Latte\Compiler\Tag;
use Latte\Essential\Nodes\PrintNode;
use Latte\Extension;
use Latte\Runtime\FilterInfo;
use Nette\Localization\ITranslator;

class TranslatorExtension extends Extension
{

	private ITranslator $translator;

	public function __construct(
		ITranslator $translator
	)
	{
		$this->translator = $translator;
	}

	public function getTags(): array
	{
		return [
			'_' => [$this, 'parseTranslate'],
			'translate' => [TranslateNode::class, 'create'],
			'translator' => [TranslatorNode::class, 'create'],
		];
	}

	public function getFilters(): array
	{
		return [
			'translate' => fn(FilterInfo $fi, ...$args): string => $this->translator->translate(...$args),
		];
	}

	public function getProviders(): array
	{
		return [
			'translator' => $this->translator,
		];
	}

	public function parseTranslate(
		Tag $tag
	): Node
	{
		$tag->outputMode = $tag::OutputKeepIndentation;
		$tag->expectArguments();
		$expression = $tag->parser->parseUnquotedStringOrExpression();
		$args = new ArrayNode();
		if ($tag->parser->stream->tryConsume(',') !== null) {
			$args = $tag->parser->parseArguments();
		}

		$prefixProp = Helpers::createLatteProperty('Prefix');

		$messageNode = new StaticCallNode(
			new NameNode('\Contributte\Translation\Helpers', NameNode::KindFullyQualified),
			new IdentifierNode('prefixMessage'),
			[
				new ArgumentNode($expression),
				new ArgumentNode(new BinaryOpNode(new VariableNode(substr($prefixProp, 1)), '??', new NullNode())),
			]
		);

		$outputNode = new PrintNode();
		$outputNode->modifier = $tag->parser->parseModifier();
		$outputNode->modifier->escape = true;
		$outputNode->expression = $messageNode;
		array_unshift($outputNode->modifier->filters, new FilterNode(new IdentifierNode('translate'), $args->toArguments()));
		return $outputNode;
	}

}
