# What is the TestReportGenerator

The Test Report Generator is a php script which creates a report from PHPUnit tests and other testing tools. Such a report can be helpful for software releases and software audits to show what got tested and what the result of such tests were. The generated output is html and css and can be styled with custom themes as desired. The report is ideal to ship with software as proof for customers that the software (i.e. updates) have been tested and perform as expected.

The advantage compared to other alternatives like `@testdox` from PHPUnit are:

* Support of multiple languages
* Customize which test results should be included in the report
* Customizable report template

## Requirements

* PHP Version >= 8.0

## Demo

https://raw.githubusercontent.com/Karaka-Management/TestReportGenerator/master/tests/TestReport.pdf

## Installation

* Option 1: Just download the repository and use it as is.
* Option 2: Download the phar `testreportgenerator.phar` from the most recent release (**recommended**)
* Option 3: Use your composer.json file by adding the code below and then run `composer update`

### Composer file

```json
{
  "name":  "karaka-management/testreportgenerator",
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/Karaka-Management/TestReportGenerator.git"
    }
  ],
  "require": {
    "karaka-management/testreportgenerator": "dev-master"
  }
}
```

## Usage

A list of arguments can be found with:

```sh
// Option 1: repository
php TestReportGenerator/src/index.php -h

// Option 2: phar
php testreportgenerator.phar -h


// Option 3: Composer
./vendor/bin/testreportgenerator -h
```

The default usage would be as follows:

```sh
php TestReportGenerator/src/index.php -d <DESTINATION_PATH> -t <TEMPLATE> -u <JUNIT_UNIT_TEST_LOG> -c <CODE_COVERAGE_REPORT> -l <LANGUAGE_FILE>
```

Afterwards navigate to the destination path in your browser i.e. and if you want to turn this report into a PDF simply use your browser to *print* the report as PDF.

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

Note: **Paths need to be absolute!**

> If you use the phar or composer installation method just replace `php TestReportGenerator/src/index.php` accordingly.

#### Example

```sh
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

### Custom template

For creating your own report template and theme please check out the default theme as a reference point (https://github.com/Karaka-Management/TestReportGenerator/tree/master/src/Theme).

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

## Preview

![sample](https://raw.githubusercontent.com/Orange-Management/TestReportGenerator/master/img/sample.jpg)
