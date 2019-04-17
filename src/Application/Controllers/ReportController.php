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
        $testView->setTemplate(!realpath($this->template) || !file_exists($this->template . '/index.tpl.php') ? __DIR__ . '/../../Theme/index' : $this->template . '/index');

        $data = [];
        $length = count($this->data);

        for ($i = 0; $i < $length; ++$i) {
            if (substr($this->data[$i], 0, 2) === '--') {
                $data[substr($this->data[$i], 2)] = $this->data[$i+1];
                ++$i;
            }
        }

        $testView->setCmdData($data);

        $langArrayKeys  = array_keys($this->langArray);
        $testReportData = [];
        $lang           = [];

        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($this->testLog));

        $order = 0;
        foreach ($this->langArray as $key => $text) {
            if (($key[0] ?? '') === ':') {
                $lang[$key] = $text;
                continue;
            }

            $testReportData[$key] = [
                'type'   => stripos($key, ':') !== false ? 'testcase' : 'testsuite',
                'status' => 0,
                'time'   => 0,
                'info'   => $text,
                'order'  => $order,
            ];

            ++$order;
        }

        $testView->setLanguage($lang);

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

        $testsuites = $dom->getElementsByTagName('testsuite');
        foreach ($testsuites as $testsuite) {
            $class = $testsuite->getAttribute('class');

            if (!isset($this->langArray[$class])) {
                continue;
            }

            $skipps   = $testcase->getElementsByTagName('skipped');
            $warnings = $testcase->getElementsByTagName('warning');
            $failures = $testcase->getElementsByTagName('failure');
            $errors   = $testcase->getElementsByTagName('error');

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

        $testView->setTestResult($testReportData);

        file_put_contents($this->destination . '/index.htm', $testView->render());
    }

    private function createOutputDir() : void
    {
        if (!file_exists($this->destiation)) {
            mkdir($this->destination, 0777, true);
        }
    }

    private function createBaseFiles() : void
    {
        $path = !realpath($this->template) || !file_exists($this->template . '/index.tpl.php') ? __DIR__ . '/../../Theme' : $this->template;
        $this->recursiveCopy($path, $this->destination);
    }

    private function recursiveCopy(string $src, string $dest) : void
    {
        if (is_file($src)) {
            copy($src, $dest);

            return;
        }

        if (!is_dir($dest)) {
            mkdir ($dest, 0777);
        }

        $dir = dir($src);
        if ($dir === false) {
            return;
        }

        while (($sub = $dir->read()) !== false) {
            if ($sub === '.' || $sub === '..' || substr($sub, -4) === '.php') {
                continue;
            }

            $this->recursiveCopy($src . '/' . $sub, $dest . '/' . $sub);
        }

        $dir->close();
    }
}
