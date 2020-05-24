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

echo "   lookup last updated file\n";
$finder = new Finder();
$finder->files()->in(ROOT_DIR . '/var/input/')->name('*-brasil-covid-data.csv')->sortByName();
$files = $finder->getIterator();

if (!count($files)) {
    die("No files found.");
}

$filename = end($files)->getPathname();

$files = [
    [$filename, ';'],
];

echo "   extracting local data from CSV files...\n";
$localReader = CovidCsvReader::localReader();

$localCases = new ReportedCases;
foreach ($files as [$file, $separator]) {
    $cases = $localReader->read($file, $separator);
    $localCases = $localCases->merge($cases);
}

// echo "   extracting national data from CSV files...\n";
// $nationalReader = CovidCsvReader::nationalReader();

// $nationalCases = new ReportedCases;
// foreach ($files as [$file, $separator]) {
//     $cases = $nationalReader->read($file, $separator);
//     $nationalCases = $nationalCases->merge($cases);
// }

$outputDir = ROOT_DIR . "/var/output/wikipedia-{$language}";
@mkdir($outputDir);

// if ($chartParser) {
//     echo "   Parsing chart...\n";
//     $contents = $chartParser->parse($nationalCases);

//     $outputFile = $outputDir . '/chart.txt';
//     file_put_contents($outputFile, $contents);

//     echo "   Chart parsed!\n";
//     echo "   Check {$outputFile}\n";
// }

if ($graphsParser) {
    echo "   Parsing graphs...\n";
    $contents = $graphsParser->parse($localCases);

    $outputFile = $outputDir . '/graphs.txt';
    file_put_contents($outputFile, $contents);

    echo "   Graphs parsed!\n";
    echo "   Check {$outputFile}\n";
}

if ($tableParser) {
    echo "   Parsing table...\n";
    $contents = $tableParser->parse($localCases);

    $outputFile = $outputDir . '/table.txt';
    file_put_contents($outputFile, $contents);

    echo "   Table parsed!\n";
    echo "   Check {$outputFile}\n";
}
