<?php

namespace App\Infrastructure\MediaWiki;

use App\Application\ParserInterface;
use App\Domain\ReportedCase;
use App\Domain\ReportedCases;

class EnglishGraphs implements ParserInterface
{
    public function parse($cases): string
    {
        $contents = $this->buildHeader();

        $contents .= "\n<div style='display: inline-block; width: 800px; vertical-align: top;'>";
        $contents .= "\n" . $this->buildTotalConfirmedCasesGraph($cases);
        $contents .= "\n" . $this->buildNewConfirmedCasesGraph($cases);
        $contents .= "\n<noinclude>";
        $contents .= "\n" . $this->buildTotalConfirmedCasesByRegionGraph($cases);
        $contents .= "\n" . $this->buildGrowthOfConfirmedCases($cases);
        $contents .= "\n</noinclude>";
        $contents .= "\n</div>";

        $contents .= "\n<div style='display: inline-block; width: 800px; vertical-align: top;'>";
        $contents .= "\n" . $this->buildTotalConfirmedDeathsGraphs($cases);
        $contents .= "\n" . $this->buildNewConfirmedDeathsGraphs($cases);
        $contents .= "\n<noinclude>";
        $contents .= "\n" . $this->buildTotalConfirmedDeathsByRegionGraph($cases);
        $contents .= "\n</noinclude>";
        $contents .= "\n</div>";

        $contents .= "\n" . $this->buildHistoricalTable($cases);

        $contents .= "\n" . $this->buildFooter();

        return $contents;
    }

    private function buildTotalConfirmedCasesGraph(ReportedCases $cases): string
    {
        $dates = implode(', ', $this->listDates());
        $data = implode(', ', $this->listTotalCumulativeCases($cases));

        return <<<GRAPH
=== Total confirmed cases ===
{{Graph:Chart
|type=line
|linewidth=2
|showSymbols=1
|width=600
|colors={{Medical cases chart/Bar colors|3}}
|showValues=
|xAxisTitle=Date
|xAxisAngle=-60
|x={$dates}
|y1Title=Total confirmed cases
|yAxisTitle=Total confirmed cases
|y1={$data}
|yGrid= |xGrid=
}}

GRAPH;
    }

    private function buildNewConfirmedCasesGraph(ReportedCases $cases): string
    {
        $dates = implode(', ', $this->listDates());
        $data = implode(', ', $this->listTotalNewCases($cases));

        return <<<GRAPH
=== New cases per day ===
{{Graph:Chart
| type=rect
| linewidth=1
| showSymbols=1
| width=600
| colors={{Medical cases chart/Bar colors|3}}
| showValues=offset:2
| xAxisTitle=Date
| xAxisAngle=-60
|x={$dates}
|y1Title=New cases
|yAxisTitle=New cases
|y1={$data}
|yGrid= |xGrid=
}}

GRAPH;
    }

    private function buildTotalConfirmedCasesByRegionGraph(ReportedCases $cases): string
    {
        $dates = implode(', ', $this->listDates());

        $northData = implode(', ', $this->listTotalCumulativeCases($cases->filterByRegion('norte')));
        $northeastData = implode(', ', $this->listTotalCumulativeCases($cases->filterByRegion('nordeste')));
        $centralwestData = implode(', ', $this->listTotalCumulativeCases($cases->filterByRegion('centro-oeste')));
        $southeastData = implode(', ', $this->listTotalCumulativeCases($cases->filterByRegion('sudeste')));
        $southData = implode(', ', $this->listTotalCumulativeCases($cases->filterByRegion('sul')));

        return <<<GRAPH
=== Total confirmed cases, by region ===
{{Graph:Chart
|type=line
|linewidth=2
|showSymbols=1
|width=600
|colors=#00FF00, #0000FF, #FFFF00, #FF0000, #00FFFF
|showValues=
|xAxisTitle=Date
|xAxisAngle=-60
|legend=
|x={$dates}
|yAxisTitle=Total confirmed cases
|y1Title=North
|y1={$northData}
|y2Title=Northeast
|y2={$northeastData}
|y3Title=Central-West
|y3={$centralwestData}
|y4Title=Southeast
|y4={$southeastData}
|y5Title=South
|y5={$southData}
|yGrid= |xGrid=
}}

GRAPH;
    }

    private function buildGrowthOfConfirmedCases(ReportedCases $cases): string
    {
        $totalConfirmedCases = $this->listTotalCumulativeCases($cases);
        $newCases = $this->listTotalNewCases($cases);

        foreach ($newCases as $key => $value) {
            if (0 === $value) {
                unset($newCases[$key]);
                unset($totalConfirmedCases[$key]);
            }
        }

        $totalConfirmedCases = implode(', ', $totalConfirmedCases);
        $newCases = implode(', ', $newCases);

        return <<<GRAPH
=== Growth of Confirmed Cases ===
{{Side box
| position=Left
| metadata=No
| above='''Growth of Confirmed Cases'''<br/><small>a rising straight line indicates exponential growth, while a horizontal line indicates linear growth</small>
| abovestyle=text-align:center
| text= {{Graph:Chart
|type=line
|linewidth=2
|width=600
|colors={{Medical cases chart/Bar colors|3}}
|showValues=
|xAxisTitle=Total confirmed cases
|xAxisAngle=-30
|xScaleType=log
|x={$totalConfirmedCases}
|yAxisTitle=New confirmed cases
|yScaleType=log
|y={$newCases}
|yGrid= |xGrid=
}}
| below=<small>Source: Brazilian Ministry of Health</small>
}}

GRAPH;
    }

    private function buildTotalConfirmedDeathsGraphs(ReportedCases $cases): string
    {
        $dates = implode(', ', $this->listDates());
        $data = implode(', ', $this->listTotalCumulativeDeaths($cases));

        return <<<GRAPH
=== Total confirmed deaths ===
{{Graph:Chart
|type=line
|linewidth=2
|showSymbols=1
|width=600
|colors={{Medical cases chart/Bar colors|1}}
|showValues=
|xAxisTitle=Date
|xAxisAngle=-60
|x={$dates}
|y1Title=Total confirmed deaths
|yAxisTitle=Total confirmed deaths
|y1={$data}
|yGrid= |xGrid=
}}

GRAPH;
    }

    private function buildNewConfirmedDeathsGraphs(ReportedCases $cases): string
    {
        $dates = implode(', ', $this->listDates());
        $data = implode(', ', $this->listTotalNewDeaths($cases));

        return <<<GRAPH
=== New deaths per day ===
{{Graph:Chart
| type=rect
| linewidth=1
| showSymbols=1
| width=600
| colors={{Medical cases chart/Bar colors|1}}
| showValues=offset:2
| xAxisTitle=Date
| xAxisAngle=-60
|x={$dates}
|y1Title=New deaths
|yAxisTitle=New deaths
|y1={$data}
|yGrid= |xGrid=
}}

GRAPH;
    }

    private function buildTotalConfirmedDeathsByRegionGraph(ReportedCases $cases): string
    {
        $dates = implode(', ', $this->listDates());

        $northData = implode(', ', $this->listTotalCumulativeDeaths($cases->filterByRegion('norte')));
        $northeastData = implode(', ', $this->listTotalCumulativeDeaths($cases->filterByRegion('nordeste')));
        $centralwestData = implode(', ', $this->listTotalCumulativeDeaths($cases->filterByRegion('centro-oeste')));
        $southeastData = implode(', ', $this->listTotalCumulativeDeaths($cases->filterByRegion('sudeste')));
        $southData = implode(', ', $this->listTotalCumulativeDeaths($cases->filterByRegion('sul')));

        return <<<GRAPH
=== Total confirmed deaths, by region ===
{{Graph:Chart
|type=line
|linewidth=2
|showSymbols=1
|width=600
|colors=#00FF00, #0000FF, #FFFF00, #FF0000, #00FFFF
|showValues=
|xAxisTitle=Date
|xAxisAngle=-60
|legend=
|x={$dates}
|yAxisTitle=Total confirmed deaths
|y1Title=North
|y1={$northData}
|y2Title=Northeast
|y2={$northeastData}
|y3Title=Central-West
|y3={$centralwestData}
|y4Title=Southeast
|y4={$southeastData}
|y5Title=South
|y5={$southData}
|yGrid= |xGrid=
}}

GRAPH;
    }

    private function buildHistoricalTable(ReportedCases $cases): string
    {
        return <<<TABLE
=== Confirmed cases and deaths, by state ===
{{2019–20_coronavirus_pandemic_data/Brazil_medical_cases}}

TABLE;
    }

    private function listTotalCumulativeCases(ReportedCases $cases): array
    {
        $data = [];

        foreach ($this->getDateInterval() as $day) {
            $data[] = $cases->getTotalCumulativeCases($day);
        }

        return $data;
    }

    private function listTotalNewCases(ReportedCases $cases): array
    {
        $data = [];

        foreach ($this->getDateInterval() as $day) {
            $data[] = $cases->getTotalNewCases($day);
        }

        return $data;
    }

    private function listTotalCumulativeDeaths(ReportedCases $cases): array
    {
        $data = [];

        foreach ($this->getDateInterval() as $day) {
            $data[] = $cases->getTotalCumulativeDeaths($day);
        }

        return $data;
    }

    private function listTotalNewDeaths(ReportedCases $cases): array
    {
        $data = [];

        foreach ($this->getDateInterval() as $day) {
            $data[] = $cases->getTotalNewDeaths($day);
        }

        return $data;
    }

    private function listDates(): array
    {
        $dates = [];

        foreach ($this->getDateInterval() as $day) {
            $dates[] = $day->format('M j');
        }

        return $dates;
    }

    private function getDateInterval(): \DatePeriod
    {
        $begin = new \DateTime('2020-02-26');
        $end = new \DateTime('today');
        $end = $end->modify('+1 day');

        $interval = new \DateInterval('P1D');

        return new \DatePeriod($begin, $interval ,$end);
    }

    private function buildHeader(): string
    {
        return <<<HEADER
== Statistics ==
<!-- THIS IS AN AUTO GENERATED SECTION -->
</noinclude>

HEADER;
    }

    private function buildFooter(): string
    {
        return <<<FOOTER
<!-- END OF AUTO GENERATED SECTION -->

FOOTER;
    }
}
