<?php declare(strict_types=1);

namespace TestReportGenerator\src\Application\Controllers;

use TestReportGenerator\src\Application\Views\TestView;

class ReportController
{
    private string $basePath      = '';

    private string $destination   = '';

    private string $testLog       = '';

    /**
     * @var array<string, string>
     */
    private array $langArray      = [];

    private ?string $template     = null;

    /**
     * @var string[]
     */
    private array $data           = [];

    private ?string $codeCoverage = null;

    private ?string $codeStyle    = null;

    private ?string $codeStyleJs  = null;

    private ?string $codeAnalysis = null;

    /**
     * @param string                $basePath     Base app path
     * @param string                $destination  Output path
     * @param string                $testLog      Path to test logs
     * @param array<string, string> $langArray    Localization
     * @param null|string           $template     Report template
     * @param null|string           $codeCoverage Path to code coverage
     * @param null|string           $codeStyle    Path to code style
     * @param null|string           $codeStyleJs  Path to js code style
     * @param null|string           $codeAnalysis Path to code analysis
     * @para null|string[]          $data         Argv data
     */
    public function __construct(
        string $basePath,
        string $destination,
        string $testLog,
        string $langArray,
        ?string $template,
        ?string $codeCoverage,
        ?string $codeStyle,
        ?string $codeStyleJs,
        ?string $codeAnalysis,
        ?array $data
    ) {
        $this->basePath     = $basePath;
        $this->destination  = $destination;
        $this->testLog      = $testLog;
        $this->langArray    = include $langArray;
        $this->template     = $template;
        $this->data         = $data ?? [];
        $this->codeCoverage = $codeCoverage;
        $this->codeStyle    = $codeStyle;
        $this->codeStyleJs  = $codeStyleJs;
        $this->codeAnalysis = $codeAnalysis;
    }

    public function createReport() : void
    {
        $this->createOutputDir();
        $this->createBaseFiles();

        $testView = new TestView();
        $testView->setTemplate($this->template === null || !\realpath($this->template) || !\file_exists($this->template . '/index.tpl.php') ? __DIR__ . '/../../Theme/index' : $this->template . '/index');

        $this->handleCmdData($testView);

        $phpDomTest = new \DOMDocument();

        $content = \file_get_contents($this->testLog);
        if ($content === false) {
            throw new \Exception('Error while reading file!');
        }

        $phpDomTest->loadXML($content);

        // todo: handle untested methods
        // suggestion: this can be done by defining every suit and test as untested in the testReportData (in handleLanguage) and than reduce the amount for every found test (in handleTests and handleSuits)

        /** @var array<string, array<string, int|string>> $testReportData */
        $testReportData = [];
        $this->handleLanguage($testReportData, $testView);
        $this->handleTests($testReportData, $phpDomTest, $testView);
        $this->handleSuits($testReportData, $phpDomTest, $testView);

        if ($this->codeCoverage !== null && \file_exists($this->codeCoverage)) {
            $this->handleCoverage($testReportData, $testView);
        }

        if ($this->codeStyle !== null && \file_exists($this->codeStyle)) {
            $this->handleStyle($testReportData, $testView);
        }

        if ($this->codeStyleJs !== null && \file_exists($this->codeStyleJs)) {
            $this->handleStyleJs($testReportData, $testView);
        }

        if ($this->codeAnalysis !== null && \file_exists($this->codeAnalysis)) {
            $this->handleAnalysis($testReportData, $testView);
        }

        $testView->setTestResult($testReportData);

        \file_put_contents(
            $this->destination . '/index.htm',
            \preg_replace('/(?s)<pre[^<]*>.*?<\/pre>(*SKIP)(*F)|(\s{2,}|\n|\t)/', ' ',  $testView->render())
        );
    }

    private function handleCmdData(TestView $testView) : void
    {
        $data   = [];
        $length = \count($this->data);

        for ($i = 0; $i < $length; ++$i) {
            if (\substr($this->data[$i], 0, 2) === '--') {
                $data[\substr($this->data[$i], 2)] = $this->data[$i + 1];
                ++$i;
            }
        }

        $testView->setCmdData($data);
    }

    /**
     * @param array<string, array<string, int|string>> $testReportData Report data
     * @param TestView                                 $testView       View
     */
    private function handleLanguage(array &$testReportData, TestView $testView) : void
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

    /**
     * @param array<string, array<string, int|string>> $testReportData Report data
     * @param \DOMDocument                             $dom            Xml dom
     * @param TestView                                 $testView       View
     */
    private function handleTests(array &$testReportData, \DOMDocument $dom, TestView $testView) : void
    {
        $testcases = $dom->getElementsByTagName('testcase');
        foreach ($testcases as $testcase) {
            $class = $testcase->getAttribute('class');
            $test  = $testcase->getAttribute('name');

            if (!isset($this->langArray[$class . ':' . $test])) {
                continue;
            }

            $testReportData[$class . ':' . $test]['time'] = $testcase->getAttribute('time');

            $skips    = $testcase->getElementsByTagName('skipped');
            $warnings = $testcase->getElementsByTagName('warning');
            $failures = $testcase->getElementsByTagName('failure');
            $errors   = $testcase->getElementsByTagName('error');

            $testView->addSkipps($skips->length);
            $testView->addWarnings($warnings->length);
            $testView->addFailures($failures->length);
            $testView->addErrors($errors->length);

            $testView->addAssertions((int) $testcase->getAttribute('assertions'));
            $testView->addDuration((float) $testcase->getAttribute('time'));

            if (!isset($testReportData[$class])) {
                $testReportData[$class]['tests']    = 0;
                $testReportData[$class]['skips']    = 0;
                $testReportData[$class]['warnings'] = 0;
                $testReportData[$class]['failures'] = 0;
                $testReportData[$class]['errors']   = 0;
            }

            ++$testReportData[$class]['tests'];
            $testReportData[$class]['skips']    += $skips->length;
            $testReportData[$class]['warnings'] += $warnings->length;
            $testReportData[$class]['failures'] += $failures->length;
            $testReportData[$class]['errors']   += $errors->length;

            $testView->incrementTests();

            if ($errors->length > 0) {
                $testReportData[$class . ':' . $test]['status'] = -4;
            } elseif ($failures->length > 0) {
                $testReportData[$class . ':' . $test]['status'] = -3;
            } elseif ($warnings->length > 0) {
                $testReportData[$class . ':' . $test]['status'] = -2;
            } elseif ($skips->length > 0) {
                $testReportData[$class . ':' . $test]['status'] = -1;
            } else {
                $testReportData[$class . ':' . $test]['status'] = 1;
            }
        }
    }

    /**
     * @param array<string, array<string, int|string>> $testReportData Report data
     * @param \DOMDocument                             $dom            Xml dom
     * @param TestView                                 $testView       View
     */
    private function handleSuits(array &$testReportData, \DOMDocument $dom, TestView $testView) : void
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

    /**
     * @param array<string, array<string, int|string>> $testReportData Report data
     * @param TestView                                 $testView       View
     */
    private function handleCoverage(array &$testReportData, TestView $testView) : void
    {
        if ($this->codeCoverage === null) {
            return;
        }

        $dom = new \DOMDocument();

        $content = \file_get_contents($this->codeCoverage);
        if ($content === false) {
            throw new \Exception('Error while reading file!');
        }

        $dom->loadXML($content);

        $classes = $dom->getElementsByTagName('class');
        foreach ($classes as $class) {
            $metrics   = $class->getElementsByTagName('metrics');

            $className = $class->getAttribute('name') . 'Test';
            $exploded  = \explode('\\', $className);

            \array_splice($exploded, 1, 0, 'tests');
            $className = \implode('\\', $exploded);

            if ($metrics->length === 0) {
                continue;
            }

            $testView->addMethods((int) $metrics[0]->getAttribute('methods'));
            $testView->addMethodsCovered((int) $metrics[0]->getAttribute('coveredmethods'));
            $testView->addStatements((int) $metrics[0]->getAttribute('statements'));
            $testView->addStatementsCovered((int) $metrics[0]->getAttribute('coveredstatements'));
            $testView->addConditionals((int) $metrics[0]->getAttribute('conditionals'));
            $testView->addConditionalsCovered((int) $metrics[0]->getAttribute('coveredconditionals'));

            if (!isset($this->langArray[$className])) {
                continue;
            }

            $testReportData[$className]['methods']             = (int) $metrics[0]->getAttribute('methods');
            $testReportData[$className]['coveredmethods']      = (int) $metrics[0]->getAttribute('coveredmethods');
            $testReportData[$className]['statements']          = (int) $metrics[0]->getAttribute('statements');
            $testReportData[$className]['coveredstatements']   = (int) $metrics[0]->getAttribute('coveredstatements');
            $testReportData[$className]['conditionals']        = (int) $metrics[0]->getAttribute('conditionals');
            $testReportData[$className]['coveredconditionals'] = (int) $metrics[0]->getAttribute('coveredconditionals');
        }
    }

    /**
     * @param array<string, array<string, int|string>> $testReportData Report data
     * @param TestView                                 $testView       View
     */
    private function handleStyle(array &$testReportData, TestView $testView) : void
    {
        if ($this->codeStyle === null) {
            return;
        }

        $dom = new \DOMDocument();

        $content = \file_get_contents($this->codeStyle);
        if ($content === false) {
            throw new \Exception('Error while reading file!');
        }

        $dom->loadXML($content);

        $cutoff = \strlen($this->basePath);

        $classes = $dom->getElementsByTagName('testsuite');
        foreach ($classes as $class) {
            $className = $class->getAttribute('name');
            $ending    = \stripos($className, '.');
            $className = \ltrim(\substr($className, $cutoff, $ending - $cutoff), '/');
            $className = \str_replace('/', '\\', $className) . 'Test';
            $exploded  = \explode('\\', $className);

            \array_splice($exploded, 1, 0, 'tests');
            $className = \implode('\\', $exploded);

            if (!isset($this->langArray[$className])) {
                continue;
            }

            $testView->incrementStyleFiles();
            $testView->addStyleErrors($error = ((int) $class->getAttribute('errors')));
            $testView->addStyleFailures($failure = ((int) $class->getAttribute('failures')));

            $testReportData[$className]['styletests']    = 1;
            $testReportData[$className]['stylesuccess']  = $error + $failure < 1 ? 1 : 0;
            $testReportData[$className]['styleerrors']   = $error;
            $testReportData[$className]['stylefailures'] = $failure;
        }
    }

    /**
     * @param array<string, array<string, int|string>> $testReportData Report data
     * @param TestView                                 $testView       View
     */
    private function handleStyleJs(array &$testReportData, TestView $testView) : void
    {
        if ($this->codeStyleJs === null) {
            return;
        }

        $dom = new \DOMDocument();

        $content = \file_get_contents($this->codeStyleJs);
        if ($content === false) {
            throw new \Exception('Error while reading file!');
        }

        $dom->loadXML($content);

        $cutoff = \strlen($this->basePath);

        $classes = $dom->getElementsByTagName('testsuite');
        foreach ($classes as $class) {
            $className = $class->getAttribute('name');
            $ending    = \stripos($className, '.');
            $className = \ltrim(\substr($className, $cutoff, $ending - $cutoff), '/');
            $className = \str_replace('/', '\\', $className) . 'Test';
            $exploded  = \explode('\\', $className);

            \array_splice($exploded, 1, 0, 'tests');
            $className = \implode('\\', $exploded);

            if (!isset($this->langArray[$className])) {
                continue;
            }

            // check if file already checked during php test (phpcs also checks js files)
            if ($testReportData[$className]['styletests'] ?? 0 < 1) {
                $testView->incrementStyleFiles();
            }

            $testView->addStyleErrors($error = ((int) $class->getAttribute('errors')));
            $testView->addStyleFailures($failure = ($error < 1 ? 1 : 0));

            $testReportData[$className]['styletests']    = ($testReportData[$className]['styletests'] ?? 0) + 1;
            $testReportData[$className]['stylesuccess']  = ($testReportData[$className]['stylesuccess'] ?? 0) + ($error + $failure < 1 ? 1 : 0);
            $testReportData[$className]['styleerrors']   = $error;
            $testReportData[$className]['stylefailures'] = $failure;
        }
    }

    /**
     * @param array<string, array<string, int|string>> $testReportData Report data
     * @param TestView                                 $testView       View
     */
    private function handleAnalysis(array &$testReportData, TestView $testView) : void
    {
        if ($this->codeAnalysis === null) {
            return;
        }

        $content = \file_get_contents($this->codeAnalysis);
        if ($content === false) {
            throw new \Exception('Error while reading file!');
        }

        $json = \json_decode($content, true);

        if (!isset($json['files'])) {
            return;
        }

        $cutoff = \strlen($this->basePath);

        foreach ($json['files'] as $name => $file) {
            $className = $name;
            $ending    = \stripos($className, '.');
            $className = \ltrim(\substr($className, $cutoff, $ending - $cutoff), '/');
            $className = \str_replace('/', '\\', $className) . 'Test';
            $exploded  = \explode('\\', $className);

            \array_splice($exploded, 1, 0, 'tests');
            $className = \implode('\\', $exploded);

            $testView->incrementStaticFileErrors();
            $testView->addStaticErrors((int) $file['errors']);

            if (!isset($this->langArray[$className])) {
                continue;
            }

            $testReportData[$className]['staticerrors'] = (int) $file['errors'];
        }
    }

    private function createOutputDir() : void
    {
        if (!\file_exists($this->destination)) {
            \mkdir($this->destination, 0777, true);
        }
    }

    private function createBaseFiles() : void
    {
        $path = $this->template === null || !\realpath($this->template) || !\file_exists($this->template . '/index.tpl.php') ? __DIR__ . '/../../Theme' : $this->template;
        $this->recursiveCopy($path, $this->destination);
    }

    private function recursiveCopy(string $src, string $dest) : void
    {
        if (\is_file($src)) {
            \copy($src, $dest);

            return;
        }

        if (!\is_dir($dest)) {
            \mkdir($dest, 0777);
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
