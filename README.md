# What is the TestReportGenerator

This TestReportGenerator creates a html report for php unit tests which can be used to create test reports for software audits. The generated output is html, css and and can be styled with custom themes as desired. The report is ideal to ship with software as proof for customers that the software (i.e. updates) have been tested and perform as expected. Such evidences sometimes are required by companies and organizations for internal or external audits.

The advantage compared to other alternatives like `@testdox` from PHPUnit are:

* Support of multiple languages
* Customize which test results should be included in the report
* Customizable report template

## Requirements

* PHP Version >= 8.0

## Demo

http://karaka.app/Inspection/Test/ReportExternal/index.htm

## Usage

A list of arguments can be found with:

```
php TestReportGenerator/src/index.php -h
```

The default usage would be:

```
php TestReportGenerator/src/index.php -d <DESTINATION_PATH> -t <TEMPLATE> -u <JUNIT_UNIT_TEST_LOG> -c <CODE_COVERAGE_REPORT> -l <LANGUAGE_FILE>
```

### Arguments

* `-h` Show help
* `-d` Destination directory
* `-t` Template of the test report (has to be a directory containing a `index.tpl.php` which is rendered as `html` file during the generation process) (*optional* no theme definition will use the default theme)
* `-u` Unit test log (`junit` style)
* `-c` Code coverage source (`coverage-clover`) (*optional*)
* `-l` Language file (`php array`)
* `-a` Phpstan report (`json` format)
* `-s` PhpCs code style report (`junit`)
* `-sj`Eslint code style report (`junit`)

Note: Paths need to be absolute.

#### Example

```
php TestReportGenerator/src/index.php \
    -b /home/oms \
    -l /home/oms/Build/Config/reportLang.php \
    -c /home/oms/tests/coverage.xml \
    -s /home/oms/Build/test/junit_phpcs.xml \
    -sj /home/oms/Build/test/junit_eslint.xml \
    -a /home/oms/Build/test/phpstan.json \
    -u /home/oms/Build/test/junit_php.xml \
    -d /home/oms/Build/test/ReportExternal \
    --version 1.0.0
```

### Custom Arguments

If you need custom arguments which are used by the template you can use `--<definition> <value>` in order to pass these values.

The default template for example allows to pass a version number via `--version 1.0.0` which is then accessed in the template by using `$this->cmdData['version]`.

## Language file structure

The language file is an array of the following form:

```php
return [
    // keys which start with `:` are considered localized strings used in the template not for the report.
    ':yourLanguageId1' => '<localized text here>',
    ':yourLanguageId2' => '<localized text here>',
    ...
    // additional key-value pairs similar to the description key can be added optionally and then used in the customized template if required (e.g. author, purpose, associated risk etc.)
    'namespace\of\unittest\class:testFunctionName' => ['description' => '<text to display>' /* optional parameters go here */],
    'namespace\of\unittest\class:testFunctionName' => ['description' => '<text to display>'],
    ...
];
```

### Localization

By prefixing a key with `:` the generator will consider these values as localization elements only used by the template. In the template you can access them via:

```php
$this->getText(':yourLanguageId');
```

### Report Parameters for Tests

If you want to show additional information in your test report per test you can provide these information as additional key-value pairs. In the template you can access them via:

```php
$result['info']['key_name'];
```

Check out the default template for an example `$result['info']['description'];`

## Preview

![sample](https://raw.githubusercontent.com/Orange-Management/TestReportGenerator/master/img/sample.jpg)
