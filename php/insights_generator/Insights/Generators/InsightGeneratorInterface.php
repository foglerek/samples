<?php namespace Anon\Insights\Generators;

use Maatwebsite\Excel\Writers\LaravelExcelWriter;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;

interface InsightGeneratorInterface
{
    public function getData();
    public function writeToExcel(LaravelExcelWriter &$excel, LaravelExcelWorksheet &$aggregateSheet = null, LaravelExcelWorksheet &$coverageDataSheet = null);
}
