<?php

define('ROOT_DIR', realpath(__DIR__ . '/..'));

require_once ROOT_DIR . '/vendor/autoload.php';

use App\Infrastructure\CovidCsvReader;
use App\Infrastructure\MediaWiki\PortugueseTable as Parser;

echo "   Extracting data from CSV file...\n";
$covidCsvReader = new CovidCsvReader();
$cases = $covidCsvReader->read(ROOT_DIR . '/var/input/current.csv');

echo "   Parsing data to portuguese...\n";
$parser = new Parser();
$contents = $parser->parse($cases);
file_put_contents(ROOT_DIR . '/var/output/portugueseTable.txt', $contents);

echo "   Portuguese table generated successfully!\n";
