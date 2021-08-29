<?php

namespace JdsDemoPlugin\Services\Persistence;

use JdsDemoPlugin\Exceptions\InvalidArgumentException;
use JdsDemoPlugin\Services\TwigTextExtractor\ConfigFactory;
use Phinx\Config\Config;
use Phinx\Migration\Manager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

class MigrationManagerFactory implements IMigrationManagerFactory
{
    public const KEY_DEFAULT_ENV = 'default_environment';
    public const KEY_ENVIRONMENTS = 'environments';

    /**
     * @var array<string,mixed>
     */
    private array $defaultConfig;
    private LoggerInterface $logger;

    /**
     * @param array<string,mixed> $defaultConfig
     */
    public function __construct(array $defaultConfig, LoggerInterface $logger)
    {
        $this->defaultConfig = $defaultConfig;
        $this->logger = $logger;
    }

    /**
     * Construct a migration manager
     * @param array<string,mixed>|null $config
     * @param string|null $env
     * @return IMigrationManager
     * @throws InvalidArgumentException
     */
    public function create(array $config = null, ?string $env = null): IMigrationManager
    {
        $config ??= $this->defaultConfig;

        /** @var array<string,mixed> $environments */
        $environments = $config[self::KEY_ENVIRONMENTS];
        $env ??= (string)$environments[self::KEY_DEFAULT_ENV];

        if (!array_key_exists($env, $environments)) {
            throw new InvalidArgumentException("Specified environment ('$env') does not exist in the provided Phinx config");
        }

        $configInstance = new Config($config);
        $manager = new Manager($configInstance, new StringInput(''), new NullOutput());

        return new MigrationManager($env, $manager, $this->logger);
    }
}
