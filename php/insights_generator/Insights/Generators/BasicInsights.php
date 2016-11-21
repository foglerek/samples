<?php namespace Anon\Insights\Generators;

use \DateTime;
use \Config;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;

use Anon\Insights\Fetchers\FooFetcher;
use Anon\Insights\Fetchers\BarFetcher;
use Anon\Insights\Fetchers\QuxFetcher;

class BasicInsights extends InsightGenerator
{

    public function writeToExcel(
        LaravelExcelWriter &$excel,
        LaravelExcelWorksheet &$aggregateSheet = null,
        LaravelExcelWorksheet &$coverageDataSheet = null
    )
    {
        $basicData = $this->getData();

        $this->log('Writing Aggregate Data ...');
        $aggregateSheet->rows($basicData['aggregate-data']);

        if ( ! Config::get('DataInsights.aggregatesOnly')) {
            $this->log('Writing Foo ...');
            $excel->sheet('Valid Foos', function($sheet) use ($basicData) {
                $sheet->fromArray($basicData['foos']);
            });

            $this->log('Writing Bars ...');
            $excel->sheet('Valid Bars', function($sheet) use ($basicData) {
                $sheet->fromArray($basicData['bars']);
            });

            $this->log('Writing Quxs ...');
            $excel->sheet('Unique Quxs', function($sheet) use ($basicData) {
                $sheet->fromArray($basicData['quxs']);
            });
        }
    }

    public function getData()
    {
        $data          = [];
        $aggregateData = [];
        $coverageData  = [];

        $dateFrom = Config::get('Insights.dateFrom');
        $dateTo   = Config::get('Insights.dateTo');

        // General Aggregate Data
        $aggregateData[] = [
            'Statistic' => 'Starting Date',
            'Value'     => $dateFrom ? $dateFrom : 'Beginning of Time',
        ];
        $aggregateData[] = [
            'Statistic' => 'End Date',
            'Value'     => $dateTo ? $dateTo : (new DateTime())->format('Y-m-d')
        ];

        // Foos
        $this->log('Generating Foos ...');
        $data['foos'] = FooFetcher::getOutput();
        $aggregateData[] = array(
            'Statistic' => 'Number of valid Foos',
            'Value'     => count($data['foos'])
        );

        // Bars
        $this->log('Generating Bars ...');
        $data['bars'] = BarFetcher::getOutput();
        $aggregateData[] = array(
            'Statistic' => 'Number of valid Bars',
            'Value'     => count($data['bars'])
        );

        // Qux
        $this->log('Generating Quxs ...');
        $data['quxs'] = QuxFetcher::getOutput();
        $aggregateData[] = array(
            'Statistic' => 'Number of Quxs',
            'Value'     => count($data['quxs'])
        );
        $aggregateData[] = array(
            'Statistic' => 'Number of individual pieces of qux children',
            'Value'     => QuxFetcher::getChildCount()
        );

        // Add Aggregate Data
        $data['aggregate-data'] = $aggregateData;
        $data['coverage-data']  = $coverageData;

        return $data;
    }
}
