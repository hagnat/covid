<?php

define('ROOT_DIR', realpath(__DIR__ . '/..'));

require_once ROOT_DIR . '/vendor/autoload.php';

use App\Infrastructure\CovidCsvReader;
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

switch (strtolower($language)) {
    case 'en':
        $tableParser = new EnglishTable();
        $graphsParser = new EnglishGraphs();
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

echo "   extracting data from CSV file...\n";
$covidCsvReader = new CovidCsvReader();
$archivedCases = $covidCsvReader->read(ROOT_DIR . '/var/input/2020-05-10-brasil-covid-data.csv', ';');
$currentCases = $covidCsvReader->read($filename, ';');

$cases = $currentCases->merge($archivedCases);

$outputDir = ROOT_DIR . "/var/output/wikipedia-{$language}";
@mkdir($outputDir);

if ($tableParser) {
    echo "   Parsing table...\n";
    $contents = $tableParser->parse($cases);

    $outputFile = $outputDir . '/table.txt';
    file_put_contents($outputFile, $contents);

    echo "   Table parsed!\n";
    echo "   Check {$outputFile}\n";
}

if ($graphsParser) {
    echo "   Parsing graphs...\n";
    $contents = $graphsParser->parse($cases);

    $outputFile = $outputDir . '/graphs.txt';
    file_put_contents($outputFile, $contents);

    echo "   Graphs parsed!\n";
    echo "   Check {$outputFile}\n";
}
