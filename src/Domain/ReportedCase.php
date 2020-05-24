<?php

declare(strict_types=1);

namespace App\Domain;

final class ReportedCase
{
    public $state;

    public $region;

    public $day;

    public $newCases;

    public $cumulativeCases;

    public $newDeaths;

    public $cumulativeDeaths;

    public $newRecoveries;

    public $cumulativeRecoveries;
}
