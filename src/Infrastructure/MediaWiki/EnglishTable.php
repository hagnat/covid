<?php

namespace App\Infrastructure\MediaWiki;

use App\Application\ParserInterface;
use App\Domain\ReportedCase;
use App\Domain\ReportedCases;

class EnglishTable implements ParserInterface
{
    public function parse($reportedCases): string
    {
        $contents = $this->buildHeader();

        foreach ($reportedCases->groupByDate() as $day => $cases) {
            $contents .= $this->buildRow($cases, new \DateTimeImmutable($day));
        }

        $contents .= "\n" . $this->buildFooter();

        return $contents;
    }

    private function buildRow(ReportedCases $cases, \DateTimeInterface $day): string
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
            $filteredCases = $cases->filterByState($state);
            $data[] = $filteredCases->getTotalCumulativeCases() ?: '';
        }
        $data = implode(' || ', $data);
        $data = preg_replace('/  /', ' ', $data);

        $row .= $data;

        $row .= "\n!rowspan=2| " . ($cases->getTotalNewCases() ? '+' . $cases->getTotalNewCases() : '=');
        $row .= "\n!rowspan=2| " . $cases->getTotalCumulativeCases();

        $row .= "\n|rowspan=2| " . ($cases->getTotalCumulativeDeaths()
            ? ($cases->getTotalNewDeaths() ? '+' . $cases->getTotalNewDeaths() : '=')
            : '');
        $row .= "\n|rowspan=2| " . ($cases->getTotalCumulativeDeaths() ?: '');

        $row .= "\n|-";
        $row .= "\n! Deaths";
        $row .= "\n| ";

        $data = [];
        foreach ($states as $key => $state) {
            $filteredCases = $cases->filterByState($state);
            $data[] = $filteredCases->getTotalCumulativeDeaths() ?: '';
        }
        $data = implode(' || ', $data);
        $data = preg_replace('/  /', ' ', $data);

        $row .= $data;

        return $row;
    }

    private function buildHeader()
    {
        return <<<HEADER
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
        return <<<FOOTER
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
{{note|1}} Official data provided by the Brazilian Ministry of Health <ref>{{cite web|url=https://covid.saude.gov.br/|title=Ministério da Saúde|date=April 2020}}</ref>.
|-
|}<noinclude>{{doc}}</noinclude>
FOOTER;
    }
}
