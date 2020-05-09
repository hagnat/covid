<?php

define('ROOT_DIR', realpath(__DIR__ . '/..'));

require_once ROOT_DIR . '/vendor/autoload.php';

use App\Infrastructure\CovidCsvReader;
use App\Infrastructure\MediaWiki\EnglishGraphs;
use App\Infrastructure\MediaWiki\EnglishTable;
use App\Infrastructure\MediaWiki\PortugueseTable;

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

echo "   extracting data from CSV file...\n";
$covidCsvReader = new CovidCsvReader();
$cases = $covidCsvReader->read(ROOT_DIR . '/var/input/current.csv');

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
