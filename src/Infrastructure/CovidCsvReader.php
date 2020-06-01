<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\ReportedCase;
use App\Domain\ReportedCases;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

final class CovidCsvReader
{
    private $io;

    public function __construct(SymfonyStyle $io)
    {
        $this->io = $io;
    }

    public function read(string $filename, string $csvSeparator): ReportedCases
    {
        $reportedCases = new ReportedCases();

        if (!($handle = fopen($filename, 'r'))) {
            die('Unable to open CSV file');
        }

        $this->io->progressStart($this->lineCount($handle));

        $headers = fgetcsv($handle, null, $csvSeparator);

        while (false !== ($data = fgetcsv($handle, null, $csvSeparator))) {
            $row = array_combine($headers, $data);

            try {
                $case = ReportedCase::fromCsv($row);
                $reportedCases->add($case);
            } catch (Throwable $e) {
                continue;
            } finally {
                $this->io->progressAdvance();
            }
        }

        $this->io->progressFinish();

        $this->io->success(sprintf('Parsed %s cases.', $reportedCases->count()));

        fclose($handle);

        return $reportedCases;
    }

    private function lineCount($handle)
    {
        rewind($handle);
        $linecount = 0;

        while (!feof($handle)) {
            $line = fgets($handle);
            ++$linecount;
        }

        rewind($handle);

        return $linecount;
    }
}
