<?php

namespace JdsDemoPlugin\Services\Persistence;

use Phinx\Wrapper\TextWrapper;

class MigrationManager
{
    public const OPT_CONFIG = 'configuration';

    private string $configPath;
    private TextWrapper $phinxApp;

    public function __construct(string $phinxConfigPath, TextWrapper $phinxApp)
    {
        $this->phinxApp = $phinxApp;
        $this->configPath = $phinxConfigPath;
        $this->phinxApp->setOption(self::OPT_CONFIG, $this->configPath);
    }

    public function migrate(): void
    {
        $result = $this->phinxApp->getMigrate();
        echo $result;
    }
}
