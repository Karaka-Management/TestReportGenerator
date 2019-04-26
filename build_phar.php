<?php
$phar = new \Phar(__DIR__ . '/testreportgenerator.phar', FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, 'testreportgenerator.phar');
$phar->startBuffering();
$phar->setStub($phar->createDefaultStub('TestReportGenerator/src/index.php', 'TestReportGenerator/src/index.php'));
$phar->buildFromDirectory(\realpath(__DIR__ . '/..'), '/((TestReportGenerator)+(\\/|\\\)+(.*?\\.)(php|css|png|js|ico|txt))$/');
$phar->stopBuffering();