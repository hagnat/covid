<?php

define('ROOT_DIR', __DIR__ . '/..');

require_once ROOT_DIR . '/vendor/autoload.php';

use App\Application\CovidTableGenerator;
use App\Infrastructure\CovidCsvReader;
use App\Infrastructure\MediaWiki\EnglishTable as Parser;

$covidCsvReader = new CovidCsvReader();
$cases = $covidCsvReader->read(ROOT_DIR . '/var/input/current.csv');

$parser = new Parser();
$contents = $parser->parse($cases);
file_put_contents(ROOT_DIR . '/var/output/englishTable.txt', $contents);

