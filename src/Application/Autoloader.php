<?php
declare(strict_types=1);

namespace TestReportGenerator\src\Application;

\spl_autoload_register('\TestReportGenerator\src\Application\Autoloader::defaultAutoloader');

final class Autoloader
{
    /**
     * @var string[]
     */
    private static $paths = [
        __DIR__ . '/../',
        __DIR__ . '/../../',
        __DIR__ . '/../../../',
    ];

    private function __construct()
    {
    }

    public static function addPath(string $path) : void
    {
        self::$paths[] = \rtrim($path, '/\\') . '/';
    }

    public static function defaultAutoloader(string $class) : void
    {
        $class = \ltrim($class, '\\');
        $class = \strtr($class, '_\\', '//');

        foreach (self::$paths as $path) {
            if (\file_exists($file = $path . $class . '.php')) {
                include_once $file;

                return;
            }
        }
    }

    public static function exists(string $class) : bool
    {
        $class = \ltrim($class, '\\');
        $class = \strtr($class, '_\\', '//');

        foreach (self::$paths as $path) {
            if (\file_exists($path . $class . '.php')) {
                return true;
            }
        }

        return false;
    }
}
