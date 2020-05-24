<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\ReportedCase;
use App\Domain\ReportedCases;
use DateTimeImmutable;

final class CovidCsvReader
{
    private $mode;

    private function __construct(string $mode)
    {
        $this->mode = $mode;
    }

    public static function nationalReader(): self
    {
        return new static('national');
    }

    public static function localReader(): self
    {
        return new static('local');
    }

    public function read(string $filename, string $csvSeparator): ReportedCases
    {
        $reportedCases = new ReportedCases();

        if (!($handle = fopen($filename, 'r'))) {
            die('Unable to open CSV file');
        }

        $headers = null;
        while (false !== ($data = fgetcsv($handle, null, $csvSeparator))) {
            if (null === $headers) {
                $headers = $data;

                continue;
            }

            $row = array_combine($headers, $data);

            if ('local' === $this->mode && (!trim($row['estado'] ?? '') || trim($row['codmun'] ?? ''))) {
                // only gets the data from states
                continue;
            }

            if ('national' === $this->mode && 'Brasil' !== $row['regiao']) {
                // only gets the data from country
                continue;
            }

            $case = $this->parseRow($row);

            if (0 == $case->cumulativeCases) {
                continue;
            }

            $reportedCases->add($case);
        }

        fclose($handle);

        return $reportedCases;
    }

    private function parseRow(array $data): ReportedCase
    {
        $reportedCase = new ReportedCase();

        $reportedCase->state = $data['estado'] ?? null;
        $reportedCase->region = $data['regiao'] ?? null;

        $reportedCase->day = DateTimeImmutable::createFromFormat('!d/m/Y', $data['data'])
            ?: DateTimeImmutable::createFromFormat('!Y-m-d', $data['data']);

        $reportedCase->cumulativeCases = $data['casosAcumulados']
            ?? $data['casosAcumulado']
            ?? 0;
        $reportedCase->cumulativeDeaths = $data['obitosAcumulados']
            ?? $data['obitosAcumulado']
            ?? 0;
        $reportedCase->cumulativeRecoveries = $data['Recuperadosnovos']
            ?? 0;

        return $reportedCase;
    }
}
