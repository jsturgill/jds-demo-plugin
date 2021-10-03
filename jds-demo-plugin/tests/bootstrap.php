<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace JdsDemoPlugin\Tests;

use JdsDemoPlugin\Services\FileSystem;

require_once(dirname(__DIR__) . "/vendor/autoload.php");

$fs = new FileSystem(__DIR__, false);
$fs->emptyDirectory(__DIR__ . "/_data/cache", [$fs, 'deleteAllButGitignore']);

/**
 * see https://github.com/Codeception/Codeception/issues/5938#issuecomment-695217739
 *
 * From user chylex comment:
 *
 * This was very confusing behavior when I started using Codeception, and still continues to trip me up.
 *
 * It's not clear at all that simply running codecept run, and then crashing with a GroupManager error
 * complaining about missing files, means I have to go into /tests/_output and delete failed. There's no
 * indication that GroupManager is trying to re-run failed tests on its own, it doesn't tell me which
 * group is supposed to have the file, so everytime it happens I go hunting through .suite.yml files before
 * I realize it's the failed tests again...
 */
$fs->emptyDirectory(__DIR__ . "/_output", [$fs, 'deleteAllButGitignore']);
