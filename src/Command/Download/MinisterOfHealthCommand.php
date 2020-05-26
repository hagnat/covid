<?php

declare(strict_types=1);

namespace App\Command\Download;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ExcelReader;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

final class MinisterOfHealthCommand extends Command
{
    protected static $defaultName = 'download:minister-of-health';

    private $logger;
    private $inputDir;
    private $tmpDir;

    public function __construct(string $inputDir, string $tmpDir)
    {
        $this->inputDir = $inputDir;
        $this->tmpDir = $tmpDir;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Downloads data from the Minister of Health, saving it in a CSV format.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $archiveFile = sprintf('%s/%s-brasil-covid-data.csv', $this->inputDir, date('Y-m-d'));

        if (file_exists($archiveFile) && date('Y-m-d H', filemtime($archiveFile)) == date('Y-m-d H')) {
            $output->writeln('<comment>File was recently updated. No need to download it again.</comment>');

            return 0;
        }

        $tmpFile = null;

        try {
            $output->writeln('Downloading current data.');
            $tmpFile = $this->downloadData();

            $output->writeln('Convert data to CSV format.');
            $this->convertToCsv($tmpFile, $archiveFile);

            $output->writeln('<info>Download complete!</info>');
        } catch (Throwable $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return 1;
        } finally {
            if ($tmpFile) {
                unlink($tmpFile);
            }
        }

        return 0;
    }

    private function downloadData(): string
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://xx9p7hp1p7.execute-api.us-east-1.amazonaws.com/prod/PortalGeral',
            CURLOPT_HTTPHEADER => [
                'X-Parse-Application-Id: unAFkcaNDeXajurGB7LChj8SgQYS2ptm',
                'TE: Trailers',
            ],
        ]);

        $result = curl_exec($curl);
        $contents = json_decode($result, true);
        $data = file_get_contents($contents['results']['0']['arquivo']['url']);

        $tmpFile = tempnam($this->tmpDir, 'hm-covid-data');
        file_put_contents($tmpFile, $data);

        return $tmpFile;
    }

    private function convertToCsv(string $excelFilename, string $archiveFilename): void
    {
        $reader = new ExcelReader();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($excelFilename);

        $writer = new CsvWriter($spreadsheet);
        $writer->setDelimiter(';');
        $writer->setEnclosure('');
        $writer->save($archiveFilename);
    }
}
