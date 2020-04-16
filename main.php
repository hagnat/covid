<?php

require_once __DIR__ . '/src/Application/ParserInterface.php';

require_once __DIR__ . '/src/Domain/ReportedCase.php';
require_once __DIR__ . '/src/Domain/ReportedCases.php';

require_once __DIR__ . '/src/Infrastructure/CovidCsvReader.php';
require_once __DIR__ . '/src/Infrastructure/MediaWiki/EnglishTable.php';
require_once __DIR__ . '/src/Infrastructure/MediaWiki/PortugueseTable.php';

use App\Application\CovidTableGenerator;
use App\Infrastructure\CovidCsvReader;
use App\Infrastructure\MediaWiki\EnglishTable as MediawikiEnglishTable;
use App\Infrastructure\MediaWiki\PortugueseTable as MediawikiPortugueseTable;

$covidCsvReader = new CovidCsvReader();
$cases = $covidCsvReader->read(__DIR__ . '/var/input/current.csv');

$englishParser = new MediawikiEnglishTable();
$englishTable = $englishParser->parse($cases);
file_put_contents(__DIR__ . '/var/output/englishTable.txt', $englishTable);

$portugueseParser = new MediawikiPortugueseTable();
$englishTable = $portugueseParser->parse($cases);
file_put_contents(__DIR__ . '/var/output/portugueseTable.txt', $englishTable);
