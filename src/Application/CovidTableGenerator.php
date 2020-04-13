<?php

namespace App\Application;

use App\Infrastructure\CovidCsvReader;
use App\Infrastructure\MediaWiki\TableGenerator as MediaWikiTableGenerator;

class CovidTableGenerator
{
	private $input;
	private $reader;
	private $parser;

	public function __construct()
	{
		$this->input = __DIR__ . '/../../data/current.csv';
		$this->output = __DIR__ . '/../../data/output.txt';

		$this->reader = new CovidCsvReader;
		$this->parser = new MediaWikiTableGenerator;
	}

	public function execute()
	{
		$reportedCases = $this->reader->read($this->input);

		$contents = $this->parser->parse($reportedCases);

		file_put_contents($this->output, $contents);
	}
}