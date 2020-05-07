<?php

namespace App\Domain;

class ReportedCases
{
    private $reportedCases = [];

    public function __construct(ReportedCase ...$cases)
    {
        foreach ($cases as $case) {
            $this->add($case);
        }
    }

    public function add(ReportedCase $case)
    {
        if (empty($this->reportedCases[$case->day->format('Y-m-d')])) {
            $this->reportedCases[$case->day->format('Y-m-d')] = [];

            ksort($this->reportedCases);
        }
        $this->reportedCases[$case->day->format('Y-m-d')][$case->state] = $case;
    }

    public function filterByRegion(string $region): ReportedCases
    {
        $filteredCases = [];

        foreach ($this->reportedCases as $day => $cases) {
            foreach ($cases as $case) {
                if (strtoupper($region) === strtoupper($case->region)) {
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
            if (!empty($cases[$state])) {
                $filteredCases[] = $cases[$state];
            }
        }

        return new ReportedCases(...array_values($filteredCases));
    }

    public function filterByDate(\DateTimeInterface $date): ReportedCases
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

    public function getLastReportedDate(): ?\DateTimeInterface
    {
        $dates = array_keys($this->reportedCases);
        $lastReportedDate = end($dates);

        return $lastReportedDate
            ? \DateTimeImmutable::createFromFormat ('!Y-m-d', $lastReportedDate)
            : null;
    }

    private function getLastReportedCases(): array
    {
        return end($this->reportedCases) ?: [];
    }
}
