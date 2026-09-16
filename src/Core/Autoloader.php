<?php
namespace Alfasic\Core;

class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register(function (string $class): void {
            $prefix = 'Alfasic\\';

            if (!str_starts_with($class, $prefix)) {
                return;
            }

            $relativeClass = substr($class, strlen($prefix));

            $file = dirname(__DIR__) . '/' . str_replace('\\', '/', $relativeClass) . '.php';

            if (file_exists($file)) {
                require $file;
            }
        });
    }
}