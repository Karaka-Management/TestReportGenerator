<?php declare(strict_types=1);
namespace TestReportGenerator\src\Application\Views;

class TestView
{
    protected $template   = '';
    protected $testresult = [];
    protected $lang       = [];
    protected $cmdData    = [];
    protected $duration   = 0.0;

    protected $errors   = 0;
    protected $warnings = 0;
    protected $skipps   = 0;
    protected $failures = 0;

    protected $warningsSuits = 0;
    protected $skippsSuits   = 0;
    protected $failuresSuits = 0;
    protected $testsSuits    = 0;

    protected $tests      = 0;
    protected $suits      = 0;
    protected $assertions = 0;

    protected $methods             = 0;
    protected $methodsCovered      = 0;
    protected $statements          = 0;
    protected $statementsCovered   = 0;
    protected $conditionals        = 0;
    protected $conditionalsCovered = 0;

    protected $styleFiles    = 0;
    protected $styleErrors   = 0;
    protected $styleFailures = 0;

    protected $staticFileErrors = 0;
    protected $staticErrors     = 0;

    public function addStaticErrors(int $staticErrors) : void
    {
        $this->staticErrors += $staticErrors;
    }

    public function incrementStaticFileErrors() : void
    {
        ++$this->staticFileErrors;
    }

    public function incrementStyleFiles() : void
    {
        ++$this->styleFiles;
    }

    public function addStyleErrors(int $styleErrors) : void
    {
        $this->styleErrors += $styleErrors;
    }

    public function addStyleFailures(int $styleFailures) : void
    {
        $this->styleFailures += $styleFailures;
    }

    public function addMethods(int $methods) : void
    {
        $this->methods += $methods;
    }

    public function addStatements(int $statements) : void
    {
        $this->statements += $statements;
    }

    public function addConditionals(int $conditionals) : void
    {
        $this->conditionals += $conditionals;
    }

    public function addMethodsCovered(int $methods) : void
    {
        $this->methodsCovered += $methods;
    }

    public function addStatementsCovered(int $statements) : void
    {
        $this->statementsCovered += $statements;
    }

    public function addConditionalsCovered(int $conditionals) : void
    {
        $this->conditionalsCovered += $conditionals;
    }

    public function addSuiteErrors(int $errors) : void
    {
        $this->errorsSuits += $errors;
    }

    public function addSuiteWarnings(int $warnings) : void
    {
        $this->warningsSuits += $warnings;
    }

    public function addSuiteSkipps(int $skipps) : void
    {
        $this->skippsSuits += $skipps;
    }

    public function addSuiteFailures(int $failures) : void
    {
        $this->failuresSuits += $failures;
    }

    public function addErrors(int $errors) : void
    {
        $this->errors += $errors;
    }

    public function addWarnings(int $warnings) : void
    {
        $this->warnings += $warnings;
    }

    public function addSkipps(int $skipps) : void
    {
        $this->skipps += $skipps;
    }

    public function addFailures(int $failures) : void
    {
        $this->failures += $failures;
    }

    public function incrementTests() : void
    {
        ++$this->tests;
    }

    public function incrementSuits() : void
    {
        ++$this->suits;
    }

    public function addAssertions(int $assertions) : void
    {
        $this->assertions += $assertions;
    }

    public function addDuration(float $duration) : void
    {
        $this->duration += $duration;
    }

    public function setLanguage(array $language) : void
    {
        $this->lang = $language;
    }

    public function setCmdData(array $data) : void
    {
        $this->cmdData = $data;
    }

    public function setTemplate(string $template) : void
    {
        $this->template = $template;
    }

    public function setTestResult(array $testresult) : void
    {
        $this->testresult = $testresult;
    }

    private function getText(string $text) : string
    {
        return $this->lang[$text] ?? 'ERROR';
    }

    public function render(...$data) : string
    {
        $ob   = '';
        $path = $this->template . '.tpl.php';

        if (!\file_exists($path)) {
            throw new \Exception($path);
        }

        try {
            \ob_start();
            /** @noinspection PhpIncludeInspection */
            $includeData = include $path;

            $ob = \ob_get_clean();

            if (\is_array($includeData)) {
                return $includeData;
            }
        } catch(\Throwable $e) {
            echo $e->getMessage();
        } finally {
            return $ob;
        }
    }
}
