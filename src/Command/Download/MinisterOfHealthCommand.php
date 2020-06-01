<?php

declare(strict_types=1);

namespace App\Command\Download;

use App\Adapter\PhpOffice\CsvWriter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ExcelReader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

final class MinisterOfHealthCommand extends Command
{
    protected static $defaultName = 'download:minister-of-health';

    private $archiveFile;
    private $inputDir;
    private $tmpDir;
    private $io;

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

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Downloads the COVID-19 pandemic data from the Brazillian Ministry of Health.');

        $archiveFile = sprintf('%s/%s-brasil-covid-data.csv', $this->inputDir, date('Y-m-d'));

        if (file_exists($archiveFile) && date('Y-m-d H', filemtime($archiveFile)) == date('Y-m-d H')) {
            $this->io->note([
                'Local archived file was recently updated.',
                'No need to download it again.',
            ]);

            return 0;
        }

        $tmpFile = null;

        try {
            $this->io->text('Downloading Excel file.');
            $data = $this->downloadData();

            $this->io->text('Save Excel file to temporary file.');
            $tmpFile = $this->downloadData();

            $this->io->text('Reading contents from Excel file.');
            $reader = new ExcelReader();
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($tmpFile);

            $this->io->text('Converting data to CSV format.');
            $writer = new CsvWriter($spreadsheet);
            $writer = $writer->withSymfonyStyle($this->io);
            $writer->setDelimiter(';');
            $writer->setEnclosure('');
            $writer->save($archiveFile);

            $this->io->success('Download complete!');
        } catch (Throwable $e) {
            $this->io->error([
                'Error downloading data.',
                $e->getMessage(),
            ]);

            return 1;
        } finally {
            if ($tmpFile) {
                $this->io->text('Removing temporary file.');
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
}
