<?php namespace Anon\Insights\Fetchers;

use \DB;
use \Exception;

use Symfony\Component\Console\Output\ConsoleOutput as Console;

abstract class DataFetcher implements DataFetcherInterface {
    protected static
        $console;

    protected static function log($msg)
    {
        if ( ! self::$console) {
            self::$console = new Console;
        }
        $name = explode('\\', get_called_class());
        self::$console->writeln("<info>--> [". $name[count($name)-1] ."]: ".$msg."</info>");
    }
}
