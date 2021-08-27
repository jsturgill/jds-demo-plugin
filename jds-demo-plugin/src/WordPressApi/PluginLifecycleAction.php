<?php

namespace JdsDemoPlugin\WordPressApi;

use Closure;
use JdsDemoPlugin\Exceptions\InvalidArgumentException;

class PluginLifecycleAction
{
    public const STAGE_UNINSTALL = 'uninstall';
    public const STAGE_ACTIVATION = 'activation';
    public const STAGE_DEACTIVATION = 'deactivation';

    public const STAGE_METHOD_MAP = [
        self::STAGE_UNINSTALL => 'registerUninstallHook',
        self::STAGE_ACTIVATION => 'registerActivationHook',
        self::STAGE_DEACTIVATION => 'registerDeactivationHook'
    ];

    private Closure $callback;
    public string $pluginBaseName;
    public bool $called = false;
    public int $executionCount = 0;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $pluginBaseName, string $stage, callable $callback)
    {
        if (!array_key_exists($stage, self::STAGE_METHOD_MAP)) {
            throw new InvalidArgumentException("unknown lifecycle stage: $stage");
        }

        if (!is_callable($callback)) {
            throw new InvalidArgumentException("callback is not callable");
        }

        $this->pluginBaseName = $pluginBaseName;
        $this->callback = Closure::fromCallable($callback);

        $methodName = self::STAGE_METHOD_MAP[$stage];
        $this->$methodName();
    }

    private function registerActivationHook(): void
    {
        add_action('activate_' . $this->pluginBaseName, [$this, 'execute']);
    }

    private function registerDeactivationHook(): void
    {
        add_action('deactivate_' . $this->pluginBaseName, [$this, 'execute']);
    }

    private function registerUninstallHook(): void
    {
        register_uninstall_hook($this->pluginBaseName, [$this, 'execute']);
    }

    public function execute(): void
    {
        $this->called = true;
        $this->executionCount++;
        ($this->callback)();
    }
}
