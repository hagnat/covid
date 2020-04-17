<?php

define('ROOT_DIR', __DIR__ . '/..');

require_once ROOT_DIR . '/vendor/autoload.php';

use App\Application\CovidTableGenerator;
use App\Infrastructure\CovidCsvReader;
use App\Infrastructure\MediaWiki\EnglishTable as Parser;

echo "   Extracting data from CSV file...\n";
$covidCsvReader = new CovidCsvReader();
$cases = $covidCsvReader->read(ROOT_DIR . '/var/input/current.csv');

echo "   Parsing data to english...\n";
$parser = new Parser();
$contents = $parser->parse($cases);
file_put_contents(ROOT_DIR . '/var/output/englishTable.txt', $contents);

echo "   English table generated successfully!\n";