<?php

return [
    'paths' => [
        'migrations' => __DIR__ . '/db/migrations',
        'seeds' => __DIR__ . '/db/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'jdsdp_migrations',
        'default_environment' => 'test',
        'test' => [
            'adapter' => 'sqlite',
            'memory' => true
        ],
    ],
    'version_order' => 'creation'
];
