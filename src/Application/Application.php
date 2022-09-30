<?php
declare(strict_types=1);

namespace TestReportGenerator\src\Application;

use TestReportGenerator\src\Application\Controllers\ReportController;

class Application
{
    private ?ReportController $reportController = null;

    /**
     * @param string[] $argv Application command line
     */
    public function __construct(array $argv)
    {
        $this->setupHandlers();

        $help        = ($key = \array_search('-h', $argv)) === false || $key === \count($argv) - 1 ? null : \trim($argv[(int) $key + 1], '" ');
        $destination = ($key = \array_search('-d', $argv)) === false || $key === \count($argv) - 1 ? null : \trim($argv[(int) $key + 1], '" ');
        $testLog     = ($key = \array_search('-u', $argv)) === false || $key === \count($argv) - 1 ? null : \trim($argv[(int) $key + 1], '" ');
        $langArray   = ($key = \array_search('-l', $argv)) === false || $key === \count($argv) - 1 ? null : \trim($argv[(int) $key + 1], '" ');
        $basePath    = ($key = \array_search('-b', $argv)) === false || $key === \count($argv) - 1 ? null : \trim($argv[(int) $key + 1], '" ');

        if (isset($help) || !isset($destination) || !isset($testLog) || !isset($langArray) || !isset($basePath)) {
            $this->printUsage();

            return;
        }

        $destination = \rtrim($destination, '/\\');
        $testLog     = \rtrim($testLog, '/\\');
        $langArray   = \rtrim($langArray, '/\\');
        $basePath    = \rtrim($basePath, '/\\');

        if (!\file_exists($testLog)) {
            echo 'File ' , $testLog , ' doesn\'t exist.' , "\n";
            return;
        }

        if (!\file_exists($langArray)) {
            echo 'File ' , $langArray , ' doesn\'t exist.' , "\n";
            return;
        }

        $this->createReport($basePath, $destination, $testLog, $langArray, $argv);
    }

    private function setupHandlers() : void
    {
        \set_exception_handler(function(\Throwable $e) : void {
            echo $e->getLine(), ': ' , $e->getMessage();
        });

        \set_error_handler(function(int $errno, string $errstr, string $errfile, int $errline) : bool {
            if (!(\error_reporting() & $errno)) {
                echo $errline , ': ' , $errfile;
            }

            return true;
        });

        \register_shutdown_function(function() : void {
            $e = \error_get_last();

            if (isset($e)) {
                echo $e['line'] , ': ' , $e['message'];
            }
        });

        \mb_internal_encoding('UTF-8');
    }

    /**
     * @param string   $basePath    Base path for app
     * @param string   $destination Output path
     * @param string   $testLog     Test log path
     * @param string   $langArray   Language file path
     * @param string[] $argv        Application command line
     */
    private function createReport(string $basePath, string $destination, string $testLog, string $langArray, array $argv) : void
    {
        $template     = ($key = \array_search('-t', $argv)) === false || $key === \count($argv) - 1 ? null : \trim($argv[(int) $key + 1], '" ');
        $codeCoverage = ($key = \array_search('-c', $argv)) === false || $key === \count($argv) - 1 ? null : \trim($argv[(int) $key + 1], '" ');
        $codeStyle    = ($key = \array_search('-s', $argv)) === false || $key === \count($argv) - 1 ? null : \trim($argv[(int) $key + 1], '" ');
        $codeStyleJs  = ($key = \array_search('-sj', $argv)) === false || $key === \count($argv) - 1 ? null : \trim($argv[(int) $key + 1], '" ');
        $codeAnalysis = ($key = \array_search('-a', $argv)) === false || $key === \count($argv) - 1 ? null : \trim($argv[(int) $key + 1], '" ');

        if ($template !== null && !\file_exists($template)) {
            echo 'File ' , $template , ' doesn\'t exist.' , "\n";
            return;
        }

        if ($codeCoverage !== null && !\file_exists($codeCoverage)) {
            echo 'File ' , $codeCoverage , ' doesn\'t exist.' , "\n";
            return;
        }

        $this->reportController = new ReportController($basePath, $destination, $testLog, $langArray, $template, $codeCoverage, $codeStyle, $codeStyleJs, $codeAnalysis, $argv);
        $this->reportController->createReport();
    }

    private function printUsage() : void
    {
        echo 'Usage: -b <BASE_PATH> -d <DESTINATION_PATH> -t <TEMPLATE> -u <JUNIT_UNIT_TEST_LOG> -c <CODE_COVERAGE_REPORT> -l <LANGUAGE_FILE>' . "\n\n";
        echo "\t" , '-b Base directory path of the project (=absolute root path)' , "\n";
        echo "\t" , '-d Destination directory' , "\n";
        echo "\t" , '-t Template of the test report (has to be a directory containing a `index.tpl.php` which is rendered as `html` file during the generation process) (*optional* no theme definition will use the default theme).' , "\n";
        echo "\t" , '-u Unit test log (phpunit `junit`)' , "\n";
        echo "\t" , '-c Code coverage source (phpunit `coverage-clover`) (*optional*)' , "\n";
        echo "\t" , '-s Code style source (code sniffer `junit`) (*optional*)' , "\n";
        echo "\t" , '-sj Code style source (eslint `junit`) (*optional*)' , "\n";
        echo "\t" , '-a Code analysis source (phpstan `coverage-clover`) (*optional*)' , "\n";
        echo "\t" , '-l Language file (`php array`)' , "\n";
    }
}
