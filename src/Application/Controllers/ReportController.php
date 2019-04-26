<?php

namespace TestReportGenerator\src\Application\Controllers;

use TestReportGenerator\src\Application\Views\TestView;

class ReportController
{
    private $destination  = '';
    private $testLog      = '';
    private $langArray    = [];
    private $template     = null;
    private $data         = [];
    private $codeCoverage = null;

    private $testView = null;

    public function __construct(
        string $destination,
        string $testLog,
        string $langArray,
        ?string $template,
        ?string $codeCoverage,
        ?array $data
    ) {
        $this->destination  = $destination;
        $this->testLog      = $testLog;
        $this->langArray    = include $langArray;
        $this->template     = $template;
        $this->data         = $data ?? '';
        $this->codeCoverage = $codeCoverage; // not used so far.
    }

    public function createReport() : void
    {
        $this->createOutputDir();
        $this->createBaseFiles();

        $testView = new TestView();
        $testView->setTemplate(!\realpath($this->template) || !\file_exists($this->template . '/index.tpl.php') ? __DIR__ . '/../../Theme/index' : $this->template . '/index');

        $this->handleCmdData($testView);

        $domTest = new \DOMDocument();
        $domTest->loadXML(\file_get_contents($this->testLog));

        // todo: handle untested methods
        // suggestion: this can be done by defining every suit and test as untested in the testReportData (in handleLanguage) and than reduce the amount for every found test (in handleTests and handleSuits)
        $testReportData = [];
        $this->handleLanguage($testReportData, $testView);
        $this->handleTests($testReportData, $domTest, $testView);
        $this->handleSuits($testReportData, $domTest, $testView);

        if (\file_exists($this->codeCoverage)) {
            $domCoverage = new \DOMDocument();
            $domCoverage->loadXML(\file_get_contents($this->codeCoverage));

            $this->handleCoverage($testReportData, $domCoverage, $testView);
        }

        $testView->setTestResult($testReportData);

        \file_put_contents($this->destination . '/index.htm', $testView->render());
    }

    private function handleCmdData($testView) : void
    {
        $data = [];
        $length = \count($this->data);

        for ($i = 0; $i < $length; ++$i) {
            if (\substr($this->data[$i], 0, 2) === '--') {
                $data[\substr($this->data[$i], 2)] = $this->data[$i+1];
                ++$i;
            }
        }

        $testView->setCmdData($data);
    }

    private function handleLanguage(array &$testReportData, $testView) : void
    {
        $lang  = [];
        $order = 0;

        foreach ($this->langArray as $key => $text) {
            if (($key[0] ?? '') === ':') {
                $lang[$key] = $text;
                continue;
            }

            $testReportData[$key] = [
                'type'   => \stripos($key, ':') !== false ? 'testcase' : 'testsuite',
                'status' => 0,
                'time'   => 0,
                'info'   => $text,
                'order'  => $order,
            ];

            ++$order;
        }

        $testView->setLanguage($lang);
    }

    private function handleTests(array &$testReportData, $dom, $testView) : void
    {
        $testcases = $dom->getElementsByTagName('testcase');
        foreach ($testcases as $testcase) {
            $class = $testcase->getAttribute('class');
            $test  = $testcase->getAttribute('name');

            if (!isset($this->langArray[$class . ':' . $test])) {
                continue;
            }

            $testReportData[$class . ':' . $test]['time'] = $testcase->getAttribute('time');

            $skipps   = $testcase->getElementsByTagName('skipped');
            $warnings = $testcase->getElementsByTagName('warning');
            $failures = $testcase->getElementsByTagName('failure');
            $errors   = $testcase->getElementsByTagName('error');

            $testView->addSkipps($skipps->length);
            $testView->addWarnings($warnings->length);
            $testView->addFailures($failures->length);
            $testView->addErrors($errors->length);

            $testView->addAssertions((int) $testcase->getAttribute('assertions'));
            $testView->addDuration((float) $testcase->getAttribute('time'));

            $testView->incrementTests();

            if ($errors->length > 0) {
                $testReportData[$class . ':' . $test]['status'] = -4;
            } elseif ($failures->length > 0) {
                $testReportData[$class . ':' . $test]['status'] = -3;
            } elseif ($warnings->length > 0) {
                $testReportData[$class . ':' . $test]['status'] = -2;
            } elseif ($skipps->length > 0) {
                $testReportData[$class . ':' . $test]['status'] = -1;
            } else {
                $testReportData[$class . ':' . $test]['status'] = 1;
            }
        }
    }

    private function handleSuits(array &$testReportData, $dom, $testView) : void
    {
        $testsuites = $dom->getElementsByTagName('testsuite');
        foreach ($testsuites as $testsuite) {
            $class = $testsuite->getAttribute('name');

            if (!isset($this->langArray[$class])) {
                continue;
            }

            $skipps   = $testsuite->getElementsByTagName('skipped');
            $warnings = $testsuite->getElementsByTagName('warning');
            $failures = $testsuite->getElementsByTagName('failure');
            $errors   = $testsuite->getElementsByTagName('error');

            $testView->addSuiteSkipps($skipps->length > 0 ? 1 : 0);
            $testView->addSuiteWarnings($warnings->length > 0 ? 1 : 0);
            $testView->addSuiteFailures($failures->length > 0 ? 1 : 0);
            $testView->addSuiteErrors($errors->length > 0 ? 1 : 0);

            $testView->incrementSuits();

            $skipps   = $testsuite->getElementsByTagName('skipped');
            $warnings = $testsuite->getElementsByTagName('warning');
            $failures = $testsuite->getElementsByTagName('failure');
            $errors   = $testsuite->getElementsByTagName('error');

            if ($errors->length > 0) {
                $testReportData[$class]['status'] = -4;
            } elseif ($failures->length > 0) {
                $testReportData[$class]['status'] = -3;
            } elseif ($warnings->length > 0) {
                $testReportData[$class]['status'] = -2;
            } elseif ($skipps->length > 0) {
                $testReportData[$class]['status'] = -1;
            } else {
                $testReportData[$class]['status'] = 1;
            }
        }
    }

    private function handleCoverage(array &$testReportData, $dom, $testView) : void
    {
        $classes = $dom->getElementsByTagName('class');
        foreach ($classes as $class) {
            $metrics = $class->getElementsByTagName('metrics');

            if ($metrics->length === 0) {
                continue;
            }

            $testView->addMethods((int) $metrics[0]->getAttribute('methods'));
            $testView->addMethodsCovered((int) $metrics[0]->getAttribute('coveredmethods'));
            $testView->addStatements((int) $metrics[0]->getAttribute('statements'));
            $testView->addStatementsCovered((int) $metrics[0]->getAttribute('coveredstatements'));
            $testView->addConditionals((int) $metrics[0]->getAttribute('conditionals'));
            $testView->addConditionalsCovered((int) $metrics[0]->getAttribute('coveredconditionals'));
        }
    }

    private function createOutputDir() : void
    {
        if (!\file_exists($this->destiation)) {
            \mkdir($this->destination, 0777, true);
        }
    }

    private function createBaseFiles() : void
    {
        $path = !\realpath($this->template) || !\file_exists($this->template . '/index.tpl.php') ? __DIR__ . '/../../Theme' : $this->template;
        $this->recursiveCopy($path, $this->destination);
    }

    private function recursiveCopy(string $src, string $dest) : void
    {
        if (\is_file($src)) {
            \copy($src, $dest);

            return;
        }

        if (!\is_dir($dest)) {
            \mkdir ($dest, 0777);
        }

        $dir = \dir($src);
        if ($dir === false) {
            return;
        }

        while (($sub = $dir->read()) !== false) {
            if ($sub === '.' || $sub === '..' || \substr($sub, -4) === '.php') {
                continue;
            }

            $this->recursiveCopy($src . '/' . $sub, $dest . '/' . $sub);
        }

        $dir->close();
    }
}
