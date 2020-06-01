<?php

declare(strict_types=1);

namespace App\Adapter\PhpOffice;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Symfony\Component\Console\Style\SymfonyStyle;

final class CsvWriter extends Csv
{
    private $io;
    private $spreadsheet;

    public function __construct(Spreadsheet $spreadsheet)
    {
        $this->spreadsheet = $spreadsheet;

        parent::__construct($spreadsheet);
    }

    public function withSymfonyStyle(SymfonyStyle $io): self
    {
        $clone = clone $this;
        $clone->io = $io;

        return $clone;
    }

    public function save($pFilename)
    {
        if ($this->io) {
            // Fetch sheet
            $sheet = $this->spreadsheet->getSheet($this->getSheetIndex());

            $this->io->progressStart($sheet->getHighestDataRow());
        }

        $result = parent::save($pFilename);

        if ($this->io) {
            $this->io->progressFinish();
        }

        return $result;
    }

    protected function writeLine($pFileHandle, array $pValues)
    {
        $result = parent::writeLine($pFileHandle, $pValues);

        if ($this->io) {
            $this->io->progressAdvance();
        }

        return $result;
    }
}
