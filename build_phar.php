<?php declare(strict_types=1);
$phar = new \Phar(__DIR__ . '/testreportgenerator.phar', FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, 'testreportgenerator.phar');
$phar->startBuffering();
$phar->setStub($phar->createDefaultStub('TestReportGenerator/src/index.php', 'TestReportGenerator/src/index.php'));

$path = \realpath(__DIR__ . '/..');
if ($path === false) {
    return;
}

$phar->buildFromDirectory($path, '/((TestReportGenerator)+(\\/|\\\)+(.*?\\.)(php|css|png|js|ico|txt))$/');
$phar->stopBuffering();
