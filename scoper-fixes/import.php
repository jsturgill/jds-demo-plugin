<?php

$files = glob(__DIR__ . DIRECTORY_SEPARATOR . '*.php');

if (false === $files) {
    /** @noinspection PhpUnhandledExceptionInspection */
    throw new Exception('unable to load scoper-fixes PHP files');
}

foreach ($files as $file) {
    if ($file !== __DIR__ . DIRECTORY_SEPARATOR . 'import.php') {
        require_once $file;
        echo "imported $file";
    }
}
