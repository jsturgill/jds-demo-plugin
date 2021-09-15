<?php

namespace JdsDemoPlugin\Tests;

use JdsDemoPlugin\Services\FileSystem;

require_once(dirname(__DIR__) . "/vendor/autoload.php");

$fs = new FileSystem(__DIR__, false);
$fs->emptyDirectory(__DIR__ . "/_data/cache", [$fs, 'deleteAllButGitignore']);
