<?php declare(strict_types = 1);

namespace Contributte\Translation\Command;

use Contributte\Translation\Extractor\LatteExtractor;
use Contributte\Translation\Extractor\NetteExtractor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Catalogue\MergeOperation;
use Symfony\Component\Translation\Catalogue\TargetOperation;
use Symfony\Component\Translation\Dumper\PoFileDumper;
use Symfony\Component\Translation\Extractor\ChainExtractor;
use Symfony\Component\Translation\Loader\PoFileLoader;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Reader\TranslationReader;
use Symfony\Component\Translation\Writer\TranslationWriter;

class ExtractCommand extends Command
{

	protected static $defaultName = 'translation:extract';

	protected static $defaultDescription = 'Extract missing translations keys from code to translation files.';

	protected function configure(): void
	{
		$this
			->setDefinition([
				new InputArgument('locale', InputArgument::REQUIRED, 'The locale'),
				new InputOption('scan-dir', 'd', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The directory where to load the messages.'),
				new InputOption('output-dir', 'o', InputOption::VALUE_OPTIONAL, 'Directory to write the messages to.'),
				new InputOption('clean', null, InputOption::VALUE_NONE, 'Should clean not found messages'),
			])
			->setName(self::$defaultName)
			->setDescription(self::$defaultDescription);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$extractor = new ChainExtractor();
		$extractor->addExtractor('php', new NetteExtractor());
		$extractor->addExtractor('latte', new LatteExtractor());

		$reader = new TranslationReader();
		$reader->addLoader('po', new PoFileLoader());

		$scanDirectory = $input->getOption('scan-dir');
		$outputDirectory = $input->getOption('output-dir');

		$currentCatalogue = new MessageCatalogue($input->getArgument('locale'));
		$reader->read($outputDirectory, $currentCatalogue);

		$extractedCatalogue = new MessageCatalogue($input->getArgument('locale'));

		foreach ($scanDirectory as $directory) {
			$extractor->extract($directory, $extractedCatalogue);
		}

		$operation = $input->getOption('clean')
			? new TargetOperation($currentCatalogue, $extractedCatalogue)
			: new MergeOperation($currentCatalogue, $extractedCatalogue);

		$operation->moveMessagesToIntlDomainsIfPossible($operation::NEW_BATCH);

		$writer = new TranslationWriter();
		$writer->addDumper('po', new PoFileDumper());

		$writer->write($operation->getResult(), 'po', ['path' => $outputDirectory]);

		return 0;
	}

}
