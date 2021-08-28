<?php

namespace JdsDemoPlugin\Services\Persistence;

use JdsDemoPlugin\Exceptions\InvalidArgumentException;
use JdsDemoPlugin\Services\TwigTextExtractor\ConfigFactory;
use Phinx\Config\Config;
use Phinx\Migration\Manager;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

class MigrationManagerFactory implements IMigrationManagerFactory
{
    public const KEY_DEFAULT_ENV = 'default_environment';
    public const KEY_ENVIRONMENTS = 'environments';
    private array $defaultConfig;

    public function __construct(array $defaultConfig)
    {
        $this->defaultConfig = $defaultConfig;
    }

    /**
     * Construct a migration manager
     * @throws InvalidArgumentException
     */
    public function create(array $config = null, $env = null): IMigrationManager
    {
        $config = $config ?? $this->defaultConfig;
        $env = $env ?? $config[self::KEY_DEFAULT_ENV];

        if (!array_key_exists($env, $config[self::KEY_ENVIRONMENTS])) {
            throw new InvalidArgumentException("Specified environment ('$env') does not exist in the provided Phinx config");
        }

        $configInstance = new Config($config);
        $manager = new Manager($configInstance, new StringInput(''), new NullOutput());

        return new MigrationManager($env, $manager);
    }
}
