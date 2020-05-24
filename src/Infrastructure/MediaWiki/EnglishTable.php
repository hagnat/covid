<?php

declare(strict_types=1);

namespace App\Infrastructure\MediaWiki;

use App\Application\ParserInterface;
use App\Domain\ReportedCases;
use Carbon\CarbonImmutable as DateTimeImmutable;
use Carbon\CarbonInterface as DateTimeInterface;
use Carbon\CarbonInterval as DateInterval;
use Carbon\CarbonPeriod as DatePeriod;

final class EnglishTable implements ParserInterface
{
    public function parse($reportedCases): string
    {
        $contents = $this->buildHeader();

        foreach ($this->getDateRange($reportedCases) as $date) {
            $previousDate = $date->sub(new DateInterval('P1D'));

            $contents .= $this->buildRow(
                $reportedCases->filterByDate($date),
                $reportedCases->filterByDate($previousDate),
                $date
            );
        }

        $contents .= "\n" . $this->buildFooter();

        return $contents;
    }

    private function buildRow(ReportedCases $cases, ReportedCases $previousDayCases, DateTimeInterface $day): string
    {
        if (0 == $cases->getTotalCumulativeCases()) {
            return '';
        }

        // $states = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];
        $states = ['AC', 'AP', 'AM', 'PA', 'RO', 'RR', 'TO', 'AL', 'BA', 'CE', 'MA', 'PB', 'PE', 'PI', 'RN', 'SE', 'DF', 'GO', 'MT', 'MS', 'ES', 'MG', 'RJ', 'SP', 'PR', 'RS', 'SC'];

        $row = "\n|-";
        $row .= "\n!rowspan=2 style='vertical-align:top'| " . $day->format('M j');
        $row .= "\n! Cases";
        $row .= "\n| ";

        $data = [];
        foreach ($states as $key => $state) {
            $casesFilteredByState = $cases->filterByState($state);
            $yesterdayCasesFilteredByState = $previousDayCases->filterByState($state);

            $totalNewCasesByState = $casesFilteredByState->getTotalCumulativeCases() - $yesterdayCasesFilteredByState->getTotalCumulativeCases();

            // $data[] = $filteredCases->getTotalCumulativeCases() ?: '';
            $data[] = $casesFilteredByState->getTotalCumulativeCases()
                ? sprintf(
                    '{{#ifeq: {{{show|total}}} | new | %s | %s }}',
                    $totalNewCasesByState ?: '',
                    $casesFilteredByState->getTotalCumulativeCases() ?: ''
                )
                : '';
        }
        $data = implode(' || ', $data);
        $data = preg_replace('/  /', ' ', $data);

        $row .= $data;

        $totalNewCases = $cases->getTotalCumulativeCases() - $previousDayCases->getTotalCumulativeCases();
        $totalNewDeaths = $cases->getTotalCumulativeDeaths() - $previousDayCases->getTotalCumulativeDeaths();

        $row .= "\n!rowspan=2| " . ($totalNewCases > 0 ? '+' . ($totalNewCases) : '=');
        $row .= "\n!rowspan=2| " . ($cases->getTotalCumulativeCases());

        $row .= "\n|rowspan=2| " . ($totalNewDeaths
            ? ($totalNewDeaths ? '+' . ($totalNewDeaths) : '=')
            : '');
        $row .= "\n|rowspan=2| " . ($cases->getTotalCumulativeDeaths() ?: '');

        $row .= "\n|-";
        $row .= "\n! Deaths";
        $row .= "\n| ";

        $data = [];
        foreach ($states as $key => $state) {
            $casesFilteredByState = $cases->filterByState($state);
            $yesterdayCasesFilteredByState = $previousDayCases->filterByState($state);

            $totalNewDeathsByState = $casesFilteredByState->getTotalCumulativeDeaths() - $yesterdayCasesFilteredByState->getTotalCumulativeDeaths();

            // $data[] = $filteredCases->getTotalCumulativeDeaths() ?: '';
            $data[] = $casesFilteredByState->getTotalCumulativeDeaths()
                ? sprintf(
                    '{{#ifeq: {{{show|total}}} | new | %s | %s }}',
                    $totalNewDeathsByState ?: '',
                    $casesFilteredByState->getTotalCumulativeDeaths() ?: ''
                )
                : '';
        }
        $data = implode(' || ', $data);
        $data = preg_replace('/  /', ' ', $data);

        $row .= $data;

        return $row;
    }

    private function buildHeader()
    {
        return <<<'HEADER'
{| class="wikitable mw-datatable mw-collapsible" style="font-size:80%; text-align: center;"
|+ style="font-size:125%" |{{nowrap|COVID-19 cases and deaths in Brazil, by state({{navbar|COVID-19 pandemic data/Brazil medical cases|mini=1|nodiv=1}})}}
!rowspan=2 colspan=2|
!colspan=7| [[North_Region,_Brazil|North]]
!colspan=9| [[Northeast_Region,_Brazil|Northeast]]
!colspan=4| [[Central-West_Region,_Brazil|Central-West]]
!colspan=4| [[Southeast_Region,_Brazil|Southeast]]
!colspan=3| [[South_Region,_Brazil|South]]
!colspan=2| Cases
!colspan=2| Deaths
|-
! {{flagicon|Acre}} <br/> [[Acre (state)|AC]]
! {{flagicon|Amapá}} <br/> [[Amapá|AP]]
! {{flagicon|Amazonas}} <br/> [[Amazonas (Brazilian state)|AM]]
! {{flagicon|Pará}} <br/> [[Pará|PA]]
! {{flagicon|Rondônia}} <br/> [[Rondônia|RO]]
! {{flagicon|Roraima}} <br/> [[Roraima|RR]]
! {{flagicon|Tocantins}} <br/> [[Tocantins|TO]]

! {{flagicon|Alagoas}} <br/> [[Alagoas|AL]]
! {{flagicon|Bahia}} <br/> [[Bahia|BA]]
! {{flagicon|Ceará}} <br/> [[Ceará|CE]]
! {{flagicon|Maranhão}} <br/> [[Maranhão|MA]]
! {{flagicon|Paraíba}} <br/> [[Paraíba|PB]]
! {{flagicon|Pernambuco}} <br/> [[Pernambuco|PE]]
! {{flagicon|Piauí}} <br/> [[Piauí|PI]]
! {{flagicon|Rio Grande do Norte}} <br/> [[Rio Grande do Norte|RN]]
! {{flagicon|Sergipe}} <br/> [[Sergipe|SE]]

! {{flagicon|Distrito Federal}} <br/> [[Federal District (Brazil)|DF]]
! {{flagicon|Goiás}} <br/> [[Goiás|GO]]
! {{flagicon|Mato Grosso}} <br/> [[Mato Grosso|MT]]
! {{flagicon|Mato Grosso do Sul}} <br/> [[Mato Grosso do Sul|MS]]

! {{flagicon|Espírito Santo}} <br/> [[Espírito Santo|ES]]
! {{flagicon|Minas Gerais}} <br/> [[Minas Gerais|MG]]
! {{flagicon|Rio de Janeiro}} <br/> [[Rio de Janeiro (state)|RJ]]
! {{flagicon|São Paulo}} <br/> [[São Paulo (state)|SP]]

! {{flagicon|Paraná}} <br/> [[Paraná (state)|PR]]
! {{flagicon|Rio Grande do Sul}} <br/> [[Rio Grande do Sul|RS]]
! {{flagicon|Santa Catarina}} <br/> [[Santa Catarina (state)|SC]]

! New
! Total
! New
! Total
HEADER;
    }

    private function buildFooter()
    {
        return <<<'FOOTER'
|-
|-
!rowspan=2 colspan=2|
! {{flagicon|Acre}} <br/> [[Acre (state)|AC]]
! {{flagicon|Amapá}} <br/> [[Amapá|AP]]
! {{flagicon|Amazonas}} <br/> [[Amazonas (Brazilian state)|AM]]
! {{flagicon|Pará}} <br/> [[Pará|PA]]
! {{flagicon|Rondônia}} <br/> [[Rondônia|RO]]
! {{flagicon|Roraima}} <br/> [[Roraima|RR]]
! {{flagicon|Tocantins}} <br/> [[Tocantins|TO]]

! {{flagicon|Alagoas}} <br/> [[Alagoas|AL]]
! {{flagicon|Bahia}} <br/> [[Bahia|BA]]
! {{flagicon|Ceará}} <br/> [[Ceará|CE]]
! {{flagicon|Maranhão}} <br/> [[Maranhão|MA]]
! {{flagicon|Paraíba}} <br/> [[Paraíba|PB]]
! {{flagicon|Pernambuco}} <br/> [[Pernambuco|PE]]
! {{flagicon|Piauí}} <br/> [[Piauí|PI]]
! {{flagicon|Rio Grande do Norte}} <br/> [[Rio Grande do Norte|RN]]
! {{flagicon|Sergipe}} <br/> [[Sergipe|SE]]

! {{flagicon|Distrito Federal}} <br/> [[Federal District (Brazil)|DF]]
! {{flagicon|Goiás}} <br/> [[Goiás|GO]]
! {{flagicon|Mato Grosso}} <br/> [[Mato Grosso|MT]]
! {{flagicon|Mato Grosso do Sul}} <br/> [[Mato Grosso do Sul|MS]]

! {{flagicon|Espírito Santo}} <br/> [[Espírito Santo|ES]]
! {{flagicon|Minas Gerais}} <br/> [[Minas Gerais|MG]]
! {{flagicon|Rio de Janeiro}} <br/> [[Rio de Janeiro (state)|RJ]]
! {{flagicon|São Paulo}} <br/> [[São Paulo (state)|SP]]

! {{flagicon|Paraná}} <br/> [[Paraná (state)|PR]]
! {{flagicon|Rio Grande do Sul}} <br/> [[Rio Grande do Sul|RS]]
! {{flagicon|Santa Catarina}} <br/> [[Santa Catarina (state)|SC]]

! New
! Total
! New
! Total
|-
!colspan=7| [[North_Region,_Brazil|North]]
!colspan=9| [[Northeast_Region,_Brazil|Northeast]]
!colspan=4| [[Central-West_Region,_Brazil|Central-West]]
!colspan=4| [[Southeast_Region,_Brazil|Southeast]]
!colspan=3| [[South_Region,_Brazil|South]]

!colspan=2| Cases
!colspan=2| Deaths
|-
| colspan="33" |
|-
| colspan="33" style="text-align: left;" | Notes:<br/>
{{note|1}} Official data provided by the Brazilian Ministry of Health<ref name=brazil>{{cite web|url=https://covid.saude.gov.br/|title=Ministério da Saúde|date=April 2020}}</ref>
|-
|}<noinclude>{{doc}}</noinclude>
FOOTER;
    }

    private function getDateRange(ReportedCases $cases): array
    {
        $begin = new DateTimeImmutable('2020-02-26');
        $interval = new DateInterval('P1D');
        $end = $cases->getLastReportedDate();
        $end = $end->add($interval);

        $period = new DatePeriod($begin, $interval, $end);

        $dates = [];
        foreach ($period as $dates[]);

        return $dates;
    }
}
