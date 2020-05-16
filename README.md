# COVID-19 Generator

This is a simple table generator for the COVID-19 Brazil medical cases historical table.

## Requirements
This code requires:

* make
* Composer
* PHP 7.1 or above
  * php-curl

## Usage

* clone this repository on your machine/server
* download the data from the Brazilian Ministry of Health (see below)
* run `make`
* validate the generated tables on `var/output`
* copy the contents of `data/output/wikipedia-en/table.txt` to https://en.wikipedia.org/wiki/Template:COVID-19_pandemic_data/Brazil_medical_cases
* copy the contents of `data/output/wikipedia-en/graphs.txt` to https://en.wikipedia.org/wiki/COVID-19_pandemic_in_Brazil/Statistics
* copy the contents of `data/output/wikipedia-pt/table.txt` to https://pt.wikipedia.org/wiki/Predefini%C3%A7%C3%A3o:Casos_de_COVID-19_no_Brasil

## Downloading the data

* visit https://covid.saude.gov.br/
* download the 'csv' file (actually an excel file)
* convert that file to csv, use ; as separator
* save the file on `/var/input/{Y-m-d}-brasil-covid-data.csv`
  * the script will look for all files named `*-brasil-covid-data.csv`, sort them by name, and use the last file
* folow the usage instructions above

## Notes

The Brazillian Ministry of Health has been providing, since mid-April, the number of cases and deaths grouped by state,
in a CSV file.

On May 12 they switched the format, and are now providing an Excel file instead.
This new data, however, does not show the number of cases prior to March 28, therefore
I've included the data file from May 11, the last 'complete' set of data that we
had before the change.

The script will automatically merge the datasets from May 11 and any new dataset available,
overriding data whenever the Minister of Health provides reviewed data for it.

## Thanks
* Leone Melo - for pointing out the CSV file that allowed to generate this table generator
* Albertoleoncio - for the basis of the bin/download-data.php script
