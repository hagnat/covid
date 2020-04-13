<?php

require_once __DIR__ . '/src/Application/CovidTableGenerator.php';

require_once __DIR__ . '/src/Domain/ReportedCase.php';
require_once __DIR__ . '/src/Domain/ReportedCases.php';

require_once __DIR__ . '/src/Infrastructure/CovidCsvReader.php';
require_once __DIR__ . '/src/Infrastructure/MediaWiki/TableGenerator.php';

use App\Application\CovidTableGenerator;

$app = new CovidTableGenerator;
$app->execute();