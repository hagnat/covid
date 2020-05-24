<?php

define('ROOT_DIR', realpath(__DIR__ . '/..'));

require_once ROOT_DIR . '/vendor/autoload.php';

use App\Domain\ReportedCases;
use App\Infrastructure\CovidCsvReader;
use App\Infrastructure\MediaWiki\EnglishChart;
use App\Infrastructure\MediaWiki\EnglishGraphs;
use App\Infrastructure\MediaWiki\EnglishTable;
use App\Infrastructure\MediaWiki\PortugueseTable;
use Symfony\Component\Finder\Finder;

$language = $argv[1] ?? null;
$validLanguages = ['en', 'pt'];

if (!in_array(strtolower($language), $validLanguages)) {
    throw new \InvalidArgumentException(sprintf('Invalid language. Expected [%s], received %s.',
        implode(',', $validLanguages),
        $language
    ));
}

$tableParser = null;
$graphsParser = null;
$chartParser = null;

switch (strtolower($language)) {
    case 'en':
        $chartParser = new EnglishChart();
        $graphsParser = new EnglishGraphs();
        $tableParser = new EnglishTable();
        break;

    case 'pt':
        $tableParser = new PortugueseTable();
        break;
}

echo "   looking for last updated file...\n";
$finder = new Finder();
$finder->files()->in(ROOT_DIR . '/var/input/')->name('*-brasil-covid-data.csv')->sortByName();
$files = $finder->getIterator();

if (!count($files)) {
    die("no files found.");
}

$filename = end($files)->getPathname();
$separator = ';';

echo "   extracting local data from CSV files...\n";
$reader = new CovidCsvReader();

$reportedCases = $reader->read($filename, $separator);

$outputDir = ROOT_DIR . "/var/output/wikipedia-{$language}";
@mkdir($outputDir);

// if ($chartParser) {
//     echo "   parsing chart...\n";
//     $contents = $chartParser->parse($reportedCases);

//     $outputFile = $outputDir . '/chart.txt';
//     file_put_contents($outputFile, $contents);

//     echo "   chart parsed!\n";
//     echo "   check {$outputFile}\n";
// }

if ($graphsParser) {
    echo "   parsing graphs...\n";
    $contents = $graphsParser->parse($reportedCases);

    $outputFile = $outputDir . '/graphs.txt';
    file_put_contents($outputFile, $contents);

    echo "   graphs parsed!\n";
    echo "   check {$outputFile}\n";
}

if ($tableParser) {
    echo "   parsing table...\n";
    $contents = $tableParser->parse($reportedCases);

    $outputFile = $outputDir . '/table.txt';
    file_put_contents($outputFile, $contents);

    echo "   table parsed!\n";
    echo "   check {$outputFile}\n";
}
