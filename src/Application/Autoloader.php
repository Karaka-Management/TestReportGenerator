<?php

namespace TestReportGenerator\src\Application;

spl_autoload_register('\TestReportGenerator\src\Application\Autoloader::defaultAutoloader');

class Autoloader
{

    public static function defaultAutoloader(string $class)
    {
        if (($classNew = self::exists($class)) !== false) {
            /** @noinspection PhpIncludeInspection */
            include_once $classNew;
        } else {
            throw new \Exception($class);
        }
    }

    public static function exists(string $class)
    {
        $class = ltrim($class, '\\');
        $class = str_replace(['_', '\\'], DIRECTORY_SEPARATOR, $class);

        if (file_exists(__DIR__ . '/../../../' . $class . '.php')) {
            return __DIR__ . '/../../../' . $class . '.php';
        } elseif (file_exists(dirname(\Phar::running(false)) . '/../../../' . $class . '.php')) {
            return dirname(\Phar::running(false)) . '/../../../' . $class . '.php';
        }

        return false;
    }
}
