<?php

declare(strict_types=1);

namespace App\Command\Wikipedia;

use App\Application\ParserInterface;
use App\Domain\ReportedCases;
use App\Infrastructure\CovidCsvReader;
use App\Infrastructure\MediaWiki\EnglishChart;
use App\Infrastructure\MediaWiki\EnglishGraphs;
use App\Infrastructure\MediaWiki\EnglishTable;
use App\Infrastructure\MediaWiki\PortugueseTable;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Throwable;

final class CreateArticlesCommand extends Command
{
    protected static $defaultName = 'wikipedia:create-articles';

    private $inputDir;
    private $outputDir;
    private $localOutputDir;
    private $io;

    public function __construct(string $inputDir, string $outputDir)
    {
        $this->inputDir = $inputDir;
        $this->outputDir = $outputDir;
        $this->localOutputDir = $outputDir;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Creates the wikipedia articles.');
        $this->addArgument('language', InputArgument::REQUIRED, 'Language to generate.');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $validLanguages = ['en', 'pt'];

        if (!in_array(mb_strtolower($input->getArgument('language')), $validLanguages)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid language. Expected [%s], received %s.',
                implode(',', $validLanguages),
                $input->getArgument('language')
            ));
        }

        $this->localOutputDir = sprintf('%s/wikipedia-%s', $this->outputDir, $input->getArgument('language'));
        @mkdir($this->localOutputDir);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->io->title(sprintf(
                'Parsing reported cases for the <info>%s</info> wikipedia.',
                'pt' === $input->getArgument('language') ? 'portuguese' : 'english'
            ));

            $this->io->text('Find latest data source.');
            $filename = $this->findLatestDataSource();

            $this->io->text('Read data from data source.');
            $reportedCases = $this->readDataSource($filename);

            $this->io->text('Get parsers for language.');
            [$table, $graph, $chart] = $this->parsers($input->getArgument('language'));

            if ($table) {
                $this->io->section('Parse table.');
                $outputFile = $this->parseReportedCases($reportedCases, $table, 'table');
                $this->io->text([
                    'Table parsed.',
                    "Check <info>{$outputFile}</info>",
                ]);
            }

            if ($graph) {
                $this->io->section('Parse graph.');
                $outputFile = $this->parseReportedCases($reportedCases, $graph, 'graph');
                $this->io->text([
                    'Graph parsed.',
                    "Check <info>{$outputFile}</info>",
                ]);
            }

            if ($chart) {
                $this->io->section('Parse chart.');
                $outputFile = $this->parseReportedCases($reportedCases, $chart, 'chart');
                $this->io->text([
                    'Chart parsed.',
                    "Check <info>{$outputFile}</info>",
                ]);
            }

            $this->io->success('Articles created!');
        } catch (Throwable $e) {
            $this->io->error([
                'Error parsing articles',
                $e->getMessage(),
                $e->getTraceAsString(),
            ]);

            return 1;
        }

        return 0;
    }

    private function parsers(string $language)
    {
        $tableParser = null;
        $graphsParser = null;
        $chartParser = null;

        switch (mb_strtolower($language)) {
            case 'en':
                $chartParser = new EnglishChart($this->io);
                $graphsParser = new EnglishGraphs($this->io);
                $tableParser = new EnglishTable($this->io);

                break;
            case 'pt':
                $tableParser = new PortugueseTable($this->io);

                break;
        }

        return [$tableParser, $graphsParser, $chartParser];
    }

    private function findLatestDataSource(): string
    {
        $files = Finder::create()
            ->files()
            ->in($this->inputDir)
            ->name('*-brasil-covid-data.csv')
            ->sortByName()
            ->getIterator();

        if (!count($files)) {
            throw new RuntimeException('No data source file found.');
        }

        return end($files)->getPathname();
    }

    private function readDataSource(string $filename): ReportedCases
    {
        $reader = new CovidCsvReader($this->io);

        return $reader->read($filename, ';');
    }

    private function parseReportedCases(ReportedCases $reportedCases, ParserInterface $parser, string $filename): string
    {
        $contents = $parser->parse($reportedCases);

        $outputFile = "{$this->localOutputDir}/{$filename}.txt";
        file_put_contents($outputFile, $contents);

        return $outputFile;
    }
}
