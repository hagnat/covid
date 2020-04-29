<?php

define('ROOT_DIR', realpath(__DIR__ . '/..'));

require_once ROOT_DIR . '/vendor/autoload.php';

use App\Infrastructure\CovidCsvReader;
use App\Infrastructure\MediaWiki\EnglishGraphs as GraphParser;
use App\Infrastructure\MediaWiki\EnglishTable as TableParser;

echo "   [english] Extracting data from CSV file...\n";
$covidCsvReader = new CovidCsvReader();
$cases = $covidCsvReader->read(ROOT_DIR . '/var/input/current.csv');

echo "   [english] Parsing table...\n";
$tableParser = new TableParser();
$contents = $tableParser->parse($cases);
file_put_contents(ROOT_DIR . '/var/output/englishTable.txt', $contents);

echo "   [english] Table generated successfully!\n";

echo "   [english] Parsing graphs...\n";
$graphParser = new GraphParser();
$contents = $graphParser->parse($cases);
file_put_contents(ROOT_DIR . '/var/output/englishGraphs.txt', $contents);

echo "   [english] Graphs generated successfully!\n";
