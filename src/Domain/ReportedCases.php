<?php

declare(strict_types=1);

namespace App\Domain;

use Carbon\Carbon as DateTime;
use Carbon\CarbonImmutable as DateTimeImmutable;
use Carbon\CarbonInterface as DateTimeInterface;
use Countable;

final class ReportedCases implements Countable
{
    private $reportedCases = [];

    public function __construct(ReportedCase ...$cases)
    {
        foreach ($cases as $case) {
            $this->add($case);
        }
    }

    public function count(): int
    {
        return array_reduce($this->reportedCases, function (int $carry, array $cases) {
            $carry += count($cases);

            return $carry;
        }, 0);
    }

    public function getArrayCopy(): array
    {
        return array_reduce($this->reportedCases, function (array $carry, array $cases) {
            array_push($carry, ...$cases);

            return $carry;
        }, []);
    }

    public function merge(ReportedCases $cases)
    {
        $mergedCases = clone $this;

        foreach ($cases->groupByDate() as $day => $casesByDate) {
            if (0 === count($mergedCases->filterByDate(new DateTime($day)))) {
                $contents = $casesByDate->getArrayCopy();
                $mergedCases->add(...$contents);
            }
        }

        return $mergedCases;
    }

    public function add(ReportedCase ...$cases)
    {
        foreach ($cases as $case) {
            if (empty($this->reportedCases[$case->day->format('Y-m-d')])) {
                $this->reportedCases[$case->day->format('Y-m-d')] = [];

                ksort($this->reportedCases);
            }
            $this->reportedCases[$case->day->format('Y-m-d')][] = $case;
        }
    }

    public function filterByRegion(string $region): ReportedCases
    {
        $filteredCases = [];

        foreach ($this->reportedCases as $day => $cases) {
            foreach ($cases as $case) {
                if (mb_strtoupper($region) === mb_strtoupper($case->region)) {
                    $filteredCases[] = $case;
                }
            }
        }

        return new ReportedCases(...array_values($filteredCases));
    }

    public function filterByState(string $state): ReportedCases
    {
        $filteredCases = [];

        foreach ($this->reportedCases as $day => $cases) {
            foreach ($cases as $case) {
                if (mb_strtoupper($state) === $case->state) {
                    $filteredCases[] = $case;
                }
            }
        }

        return new ReportedCases(...array_values($filteredCases));
    }

    public function filterByDate(DateTimeInterface $date): ReportedCases
    {
        $filteredCases = $this->reportedCases[$date->format('Y-m-d')] ?? [];

        return new ReportedCases(...array_values($filteredCases));
    }

    public function groupByDate(): array
    {
        $groups = [];

        foreach ($this->reportedCases as $date => $cases) {
            $groups[$date] = new ReportedCases(...array_values($cases));
        }

        return $groups;
    }

    public function getTotalNewCases(): int
    {
        return array_reduce($this->getLastReportedCases(), function (int $carry, ReportedCase $case) {
            $carry += $case->newCases;

            return $carry;
        }, $initial = 0);
    }

    public function getTotalCumulativeCases(): int
    {
        return array_reduce($this->getLastReportedCases(), function (int $carry, ReportedCase $case) {
            $carry += $case->cumulativeCases;

            return $carry;
        }, $initial = 0);
    }

    public function getTotalNewDeaths(): int
    {
        return array_reduce($this->getLastReportedCases(), function (int $carry, ReportedCase $case) {
            $carry += $case->newDeaths;

            return $carry;
        }, $initial = 0);
    }

    public function getTotalCumulativeDeaths(): int
    {
        return array_reduce($this->getLastReportedCases(), function (int $carry, ReportedCase $case) {
            $carry += $case->cumulativeDeaths;

            return $carry;
        }, $initial = 0);
    }

    public function getTotalCumulativeRecoveries(): int
    {
        return array_reduce($this->getLastReportedCases(), function (int $carry, ReportedCase $case) {
            $carry += (int) $case->cumulativeRecoveries;

            return $carry;
        }, $initial = 0);
    }

    public function getLastReportedDate(): ?DateTimeInterface
    {
        $dates = array_keys($this->reportedCases);
        $lastReportedDate = end($dates);

        return $lastReportedDate
            ? DateTimeImmutable::createFromFormat('!Y-m-d', $lastReportedDate)
            : null;
    }

    private function getLastReportedCases(): array
    {
        return end($this->reportedCases) ?: [];
    }
}
