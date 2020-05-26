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
use Symfony\Component\Finder\Finder;
use Throwable;

final class CreateArticlesCommand extends Command
{
    protected static $defaultName = 'wikipedia:create-articles';

    private $inputDir;
    private $outputDir;
    private $localOutputDir;

    public function __construct(string $inputDir, string $outputDir)
    {
        $this->inputDir = $inputDir;
        $this->outputDir = $outputDir;
        $this->localOutputDir = $outputDir;

        parent::__construct();
    }

    private function setup(InputInterface $input)
    {
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

    protected function configure()
    {
        $this->setDescription('Creates the wikipedia articles.');
        $this->addArgument('language', InputArgument::REQUIRED, 'Language to generate.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $output->writeln('Setup.');
            $this->setup($input);

            $output->writeln(sprintf(
                'Parsing reported cases for the <info>%s</info> wikipedia.',
                'pt' === $input->getArgument('language') ? 'portuguese' : 'english'
            ));

            $output->writeln('Find latest data source.');
            $filename = $this->findLatestDataSource();

            $output->writeln('Read data from data source.');
            $reportedCases = $this->readDataSource($filename);

            $output->writeln('Get parsers for language.');
            [$table, $graph, $chart] = $this->parsers($input->getArgument('language'));

            if ($table) {
                $output->writeln('Parse table.');
                $outputFile = $this->parseReportedCases($reportedCases, $table, 'table');
                $output->writeln("Table parsed. Check <info>{$outputFile}</info>");
            }

            if ($graph) {
                $output->writeln('Parse graph.');
                $outputFile = $this->parseReportedCases($reportedCases, $graph, 'graph');
                $output->writeln("Graph parsed. Check <info>{$outputFile}</info>");
            }

            if ($chart) {
                $output->writeln('Parse chart.');
                $outputFile = $this->parseReportedCases($reportedCases, $chart, 'chart');
                $output->writeln("Chart parsed. Check <info>{$outputFile}</info>");
            }

            $output->writeln('<info>Articles created!</info>');
        } catch (Throwable $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

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
                $chartParser = new EnglishChart();
                $graphsParser = new EnglishGraphs();
                $tableParser = new EnglishTable();

                break;
            case 'pt':
                $tableParser = new PortugueseTable();

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
        $reader = new CovidCsvReader();

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
