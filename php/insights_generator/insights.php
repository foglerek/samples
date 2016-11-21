<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Maatwebsite\Excel\Writers\LaravelExcelWriter;

use Anon\Insights\Generators\InsightGeneratorInterface as InsightGenerator;

class Insights extends Command {

    const BASIC_DATA        = 1;
    const GEOGRAPHIC_DATA   = 2;
    const INDUSTRY_DATA     = 4;
    const GENDER_DATA       = 8;
    const EDUCATION_DATA    = 16;

    protected
        $tasks = null;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'insights:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates an Excel of Data Insights for the specified dates.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        InsightGenerator $BasicInsights,
        InsightGenerator $LocationInsights,
        InsightGenerator $IndustryInsights,
        InsightGenerator $GenderInsights,
        InsightGenerator $EducationInsights
    )
    {
        parent::__construct();
        $this->BasicInsights       = $BasicInsights;
        $this->LocationInsights    = $LocationInsights;
        $this->IndustryInsights    = $IndustryInsights;
        $this->GenderInsights      = $GenderInsights;
        $this->EducationInsights   = $EducationInsights;
    }

    public function readOptions()
    {
        $this->tasks = $this->getTasks();
        Config::set('Insights.dateFrom', $this->option('from'));
        Config::set('Insights.dateTo',   $this->option('to'));
        Config::set('Insights.debug',    $this->option('debug') !== false ? true : false);
        Config::set('Insights.aggregatesOnly', $this->option('aggregates-only') !== false ? true : false);
    }

    public function setupEnvironment()
    {
        Log::useFiles('php://stdout', 'info');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->setupEnvironment();

        $this->readOptions();

        $this->info('Starting Task ...');

        $fileName = 'Insights_' . (new DateTime())->format('Y-m-d_H:i:s');

        if (Config::get('DataInsights.debug')) {
            $fileName .= '_DEBUG';
        }

        $excelBin = \Excel::create($fileName, function($excel) {
            // Instantiate Aggregate Data Sheet
            $aggregateSheet = $excel->sheet('Aggregate Data')->getSheet();
            $coverageSheet  = $excel->sheet('Data Coverage')->getSheet();

            if ( $this->taskEnabled(self::BASIC_DATA) ) {
                $this->info('Generating Basic Data ...');
                $this->BasicInsights->writeToExcel($excel, $aggregateSheet);
            }

            if ( $this->taskEnabled(self::GEOGRAPHIC_DATA) ) {
                $this->info('Generating Geographic Data ...');
                $this->LocationInsights->writeToExcel($excel, $aggregateSheet, $coverageSheet);

            }

            if ( $this->taskEnabled(self::INDUSTRY_DATA) ) {
                $this->info('Generating Industry Data ...');
                $this->IndustryInsights->writeToExcel($excel, $aggregateSheet, $coverageSheet);

            }

            if ( $this->taskEnabled(self::GENDER_DATA) ) {
                $this->info('Generating Gender Data ...');
                $this->GenderInsights->writeToExcel($excel, $aggregateSheet, $coverageSheet);
            }

            if ( $this->taskEnabled(self::EDUCATION_DATA) ) {
                $this->info('Generating Education Data ...');
                $this->EducationInsights->writeToExcel($excel, $aggregateSheet, $coverageSheet);
            }

            $this->info('Generating Excel File ...');

        })->store('xlsx', '/tmp');

        // Check which tasks to run...
        $this->info("Done! Wrote to /tmp/$fileName".".xlsx");
    }

    protected function taskEnabled($task)
    {
        return ! $this->tasks || ( $this->tasks & $task );
    }

    protected function getTasks()
    {
        return array_reduce($this->option('tasks'),
            function($tasks, $task) {
                return $tasks | constant("Insights::$task");
            },
        0);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            //array('example', InputArgument::REQUIRED, 'An example argument.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('tasks', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                "Specify tasks to run.\nRemember to add each task separately (-t TASK_ONE -t TASK_TWO ...)\nAvailable tasks:\n\t".
                implode("\n\t", array_keys((new ReflectionClass(__CLASS__))->getConstants()))."\n",
            []),
            array('from', null, InputOption::VALUE_OPTIONAL, 'From-Date to limit insights by.', null),
            array('to',   null, InputOption::VALUE_OPTIONAL, 'To-Date to limit insights by.', (new DateTime())->format('Y-m-d')),
            array('debug', 'd', InputOption::VALUE_OPTIONAL, 'Output and Excel verbosity set to debug.', false),
            array('aggregates-only', 'a', InputOption::VALUE_OPTIONAL, 'Output only aggregate data.', false)
        );
    }
}
