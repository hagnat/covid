# COVID-19 Generator

This is a simple table generator for the COVID-19 Brazil medical cases historical table.

## Requirements
This code requires PHP 7.1 or above.

## Usage
To use it
* download the current CSV file from https://covid.saude.gov.br
* update the `var/input/current.csv` file
* run `make`
* copy the contents of `data/output/englishTable.txt` to https://en.wikipedia.org/wiki/Template:2019%E2%80%9320_coronavirus_pandemic_data/Brazil_medical_cases
* copy the contents of `data/output/portugueseTable.txt` to https://pt.wikipedia.org/wiki/Predefini%C3%A7%C3%A3o:Casos_de_COVID-19_no_Brasil

## Thanks
* Leone Melo - for pointing out the CSV file that allowed to generate this table generator