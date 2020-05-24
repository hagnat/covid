# COVID-19 Generator

This is a simple table generator for the COVID-19 Brazil medical cases historical table.

## Requirements
This code requires:

* make
* Composer
* PHP 7.2 or above
  * php-curl
  * php-gd
  * php-xml
  * php-zip

## Usage

* clone this repository on your machine/server
* run `make`
* validate the generated tables on `var/output`
* copy the contents of `data/output/wikipedia-en/table.txt` to https://en.wikipedia.org/wiki/Template:COVID-19_pandemic_data/Brazil_medical_cases
* copy the contents of `data/output/wikipedia-en/graphs.txt` to https://en.wikipedia.org/wiki/COVID-19_pandemic_in_Brazil/Statistics
* copy the contents of `data/output/wikipedia-pt/table.txt` to https://pt.wikipedia.org/wiki/Predefini%C3%A7%C3%A3o:Casos_de_COVID-19_no_Brasil

## Thanks
* Leone Melo - for pointing out the CSV file that allowed to generate this table generator
* Albertoleoncio - for the basis of the bin/download-data.php script

## Todo
* create a Dockerfile
* parse a graph with cases/deaths by major capitals
  * code is already there, but ministry of health's data does not help -- use BrasilIO's data ?
* generate a SVG map based on BrasilIO's data
