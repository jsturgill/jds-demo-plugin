<?php

/** @noinspection PhpUndefinedConstantInspection */

/**
 * Phinx config file
 */

global $wpdb;

if (null === $wpdb) {
    // TODO test environment support (mock $wpdb?)
    /** @noinspection PhpUnhandledExceptionInspection */
    throw new Exception("Migrations must be run within WordPress");
}

$hostComponents = explode(':', DB_HOST);

if (count($hostComponents) > 2) {
    /** @noinspection PhpUnhandledExceptionInspection */
    throw new Exception("Malformed host -- must only have one colon: $hostComponents");
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
            // TODO test configuration
            'adapter' => 'mysql',
            'host' => 'localhost',
            'name' => 'development_db',
            'user' => 'root',
            'pass' => '',
            'port' => '3306',
            'charset' => 'utf8',
        ],
    ],
    'version_order' => 'creation'
];
