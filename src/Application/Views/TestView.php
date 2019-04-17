<?php
namespace TestReportGenerator\src\Application\Views;

class TestView
{
    protected $template = '';
    protected $testresult = [];
    protected $lang = [];
    protected $cmdData = [];

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

        if (!file_exists($path)) {
            throw new \Exception($path);
        }

        try {
            ob_start();
            /** @noinspection PhpIncludeInspection */
            $includeData = include $path;

            $ob = ob_get_clean();

            if (is_array($includeData)) {
                return $includeData;
            }
        } catch(\Throwable $e) {
            echo $e->getMessage();
        } finally {
            return $ob;
        }
    }
}
