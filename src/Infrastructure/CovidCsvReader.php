<?php

namespace App\Infrastructure;

use App\Domain\ReportedCase;
use App\Domain\ReportedCases;

class CovidCsvReader
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
        $reportedCases = new ReportedCases;

        if (!($handle = fopen($filename, "r"))) {
            die ('Unable to open CSV file');
        }

        $headers = null;
        while (false !== ($data = fgetcsv($handle, null, $csvSeparator))) {
            if (null === $headers) {
                $headers = $data;
                continue;
            }

            $row = array_combine($headers, $data);

            if ('local' === $this->mode && (empty($row['estado'] ?? '') || !empty($row['municipio'] ?? ''))) {
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
        $reportedCase = new ReportedCase;

        $reportedCase->day = \DateTimeImmutable::createFromFormat('!d/m/Y', $data['data']) ?: \DateTimeImmutable::createFromFormat('!Y-m-d', $data['data']);
        $reportedCase->state = $data['estado'];
        $reportedCase->region = $data['regiao'];
        // $reportedCase->newCases = $data['casosNovos'];
        $reportedCase->cumulativeCases = $data['casosAcumulados'] ?? $data['casosAcumulado'];
        // $reportedCase->newDeaths = $data['obitosNovos'];
        $reportedCase->cumulativeDeaths = $data['obitosAcumulados'] ?? $data['obitosAcumulado'];

        $reportedCase->cumulativeRecoveries = $data['Recuperadosnovos'] ?? 0;

        return $reportedCase;
    }
}
