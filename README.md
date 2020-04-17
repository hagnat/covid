# COVID-19 Generator

This is a simple table generator for the COVID-19 Brazil medical cases historical table.

## Requirements
This code requires:

* make
* PHP 7.1 or above
  * php-curl

## Usage

* clone this repository on your machine/server
* run `make`
* validate the generated tables on `var/output`
* copy the contents of `data/output/englishTable.txt` to https://en.wikipedia.org/wiki/Template:2019%E2%80%9320_coronavirus_pandemic_data/Brazil_medical_cases
* copy the contents of `data/output/portugueseTable.txt` to https://pt.wikipedia.org/wiki/Predefini%C3%A7%C3%A3o:Casos_de_COVID-19_no_Brasil

## Thanks
* Leone Melo - for pointing out the CSV file that allowed to generate this table generator
* Albertoleoncio - for the basis of the bin/download-data.php script
