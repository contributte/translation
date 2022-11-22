<?php declare(strict_types = 1);

namespace Contributte\Translation\Extractor;

use Latte\MacroTokens;
use Latte\Parser;
use Latte\PhpWriter;
use Latte\Token;
use LogicException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Extractor\AbstractFileExtractor;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\MessageCatalogue;

class LatteExtractor extends AbstractFileExtractor implements ExtractorInterface
{

	private string $prefix = '';


	public function extract($resource, MessageCatalogue $catalogue)
	{
		$files = $this->extractFiles($resource);

		foreach ($files as $file) {
			$this->extractFile((string) $file, $catalogue);
		}
	}

	public function setPrefix(string $prefix)
	{
		$this->prefix = $prefix;
	}

	protected function canBeExtracted(string $file)
	{
		return $this->isFile($file) && in_array(pathinfo($file, \PATHINFO_EXTENSION), ['latte', 'phtml'], true);
	}

	protected function extractFromDirectory($directory)
	{
		if (!class_exists(Finder::class)) {
			throw new LogicException(sprintf('You cannot use "%s" as the "symfony/finder" package is not installed. Try running "composer require symfony/finder".', static::class));
		}

		$finder = new Finder();

		return $finder->files()->name(['*.latte', '*.phtml'])->in($directory);

	}

	protected function extractFile(string $file, MessageCatalogue $catalogue): void
	{
		$parser = new Parser();

		foreach ($parser->parse(file_get_contents($file)) as $token) {
			if ($token->type !== Token::MACRO_TAG) {
				continue;
			}

			if ($token->name !== '_') {
				continue;
			}

			$args = new MacroTokens($token->value);
			$writer = new PhpWriter($args, $token->modifiers);

			$message = $writer->write('%node.word');

			if (in_array(substr(trim($message), 0, 1), ['"', '\''], TRUE)) {
				$message = substr(trim($message), 1, -1);
			} elseif (substr(trim($message), 0, 1) === '(') {
				$message = substr(trim($message), 2, -2);
			}

			$catalogue->set(($this->prefix !== '' ? $this->prefix . '.' : '') . $message, $message);
		}
	}

}
