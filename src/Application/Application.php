<?php

namespace TestReportGenerator\src\Application;

use TestReportGenerator\src\Application\Controllers\ReportController;

class Application
{
    private $reportController = null;

    public function __construct(array $argv)
    {
        $this->setupHandlers();

        $help        = ($key = array_search('-h', $argv)) === false || $key === count($argv) - 1 ? null : trim($argv[$key + 1], '" ');
        $destination = ($key = array_search('-d', $argv)) === false || $key === count($argv) - 1 ? null : trim($argv[$key + 1], '" ');
        $testLog     = ($key = array_search('-u', $argv)) === false || $key === count($argv) - 1 ? null : trim($argv[$key + 1], '" ');
        $langArray   = ($key = array_search('-l', $argv)) === false || $key === count($argv) - 1 ? null : trim($argv[$key + 1], '" ');

        if (isset($help) || !isset($destination) || !isset($testLog) || !isset($langArray)) {
            $this->printUsage();
        } else {
            $destination = rtrim($destination, '/\\');
            $testLog     = rtrim($testLog, '/\\');
            $langArray   = rtrim($langArray, '/\\');

            if (!file_exists($testLog)) {
                echo 'File ' . $testLog . ' doesn\'t exist.' . "\n";
                return;
            }

            if (!file_exists($langArray)) {
                echo 'File ' . $langArray . ' doesn\'t exist.' . "\n";
                return;
            }

            $this->createReport($destination, $testLog, $langArray, $argv);
        }
    }

    private function setupHandlers()
    {
        set_exception_handler(function(\Throwable $e) {
            echo $e->getLine(), ': ' , $e->getMessage();
        });

        set_error_handler(function(int $errno, string $errstr, string $errfile, int $errline) {
            if (!(error_reporting() & $errno)) {
                echo $errline , ': ' , $errfile;
            }
        });

        register_shutdown_function(function() {
            $e = error_get_last();

            if (isset($e)) {
                echo $e['line'] , ': ' , $e['message'];
            }
        });

        mb_internal_encoding('UTF-8');
    }

    private function createReport(string $destination, string $testLog, string $langArray, array $argv)
    {
        $template     = ($key = array_search('-t', $argv)) === false || $key === count($argv) - 1 ? null : trim($argv[$key + 1], '" ');
        $codeCoverage = ($key = array_search('-c', $argv)) === false || $key === count($argv) - 1 ? null : trim($argv[$key + 1], '" ');
        $version      = ($key = array_search('-v', $argv)) === false || $key === count($argv) - 1 ? null : trim($argv[$key + 1], '" ');

        if ($template !== null && !file_exists($template)) {
            echo 'File ' . $template . ' doesn\'t exist.' . "\n";
            return;
        }

        if ($codeCoverage !== null && !file_exists($codeCoverage)) {
            echo 'File ' . $codeCoverage . ' doesn\'t exist.' . "\n";
            return;
        }

        $this->reportController = new ReportController($destination, $testLog, $langArray, $template, $codeCoverage, $argv);
        $this->reportController->createReport();
    }

    private function printUsage()
    {
        echo 'Usage: -d <DESTINATION_PATH> -t <TEMPLATE> -u <JUNIT_UNIT_TEST_LOG> -c <CODE_COVERAGE_REPORT> -l <LANGUAGE_FILE>' . "\n\n";
        echo "\t" . '-d Destination directory (*optional* no theme definition will use the default theme).' . "\n";
        echo "\t" . '-t Template of the test report (has to be a directory containing a `index.tpl.php` which is rendered as `html` file during the generation process)' . "\n";
        echo "\t" . '-u Unit test log (`junit` style)' . "\n";
        echo "\t" . '-c Code coverage source (`coverage-clover`) (*optional*)' . "\n";
        echo "\t" . '-l Language file (`php array`)' . "\n";
    }
}
