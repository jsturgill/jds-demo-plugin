<?php

/** @noinspection PhpUndefinedConstantInspection */

use JdsDemoPlugin\Exceptions\CommandFailureException;

require_once(__DIR__ . '/vendor/autoload.php');

/**
 * Phinx config file
 *
 * Must be loaded inside of WordPress because it depends on $wpdb and the WordPress
 * database configuration constants -- or with (at a minimum) $wpdb set to some non-null
 * value.
 */

global $wpdb;

if (null === $wpdb) {
    /** @noinspection PhpUnhandledExceptionInspection */
    throw new CommandFailureException("Migrations must be run within WordPress");
}

$hostComponents = explode(':', DB_HOST);

if (count($hostComponents) > 2) {
    /** @noinspection PhpUnhandledExceptionInspection */
    throw new Exception("Malformed host -- must only have one colon: " . DB_HOST);
}

return
    [
        'paths' => [
            'migrations' => __DIR__ . '/db/migrations',
            'seeds' => __DIR__ . '/db/seeds',
        ],
        'environments' => [
            'default_migration_table' => $wpdb->get_blog_prefix() . 'jdsdp_migrations',
            'default_environment' => 'wordpress',
            'wordpress' => [
                'adapter' => 'mysql',
                'host' => $hostComponents[0],
                'name' => DB_NAME,
                'user' => DB_USER,
                'pass' => DB_PASSWORD,
                'port' => count($hostComponents) === 2
                    ? $hostComponents[1]
                    : '3306',
                'charset' => 'utf8',
            ],
            'test' => [
                'adapter' => 'sqlite',
                'memory' => true
            ],
        ],
        'version_order' => 'creation'
    ];
