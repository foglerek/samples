<?php namespace Anon\Insights\Generators;

use Symfony\Component\Console\Output\ConsoleOutput as Console;

abstract class InsightGenerator implements InsightGeneratorInterface {
    protected
        $console;

    protected function log($msg) {
        if ( ! $this->console) {
            $this->console = new Console;
        }
        $name = explode('\\', get_called_class());
        $this->console->writeln("<info>-> [". $name[count($name)-1] ."]: ".$msg."</info>");
    }
}
