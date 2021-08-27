<?php

namespace JdsDemoPlugin\WordPressApi;

use JdsDemoPlugin\Exceptions\InvalidArgumentException;

class PluginLifecycleActionFactory implements IPluginLifecycleActionFactory
{
    /**
     * @throws InvalidArgumentException
     */
    public function createAction(string $pluginBaseName, string $stage, callable $callable): PluginLifecycleAction
    {
        return new PluginLifecycleAction($pluginBaseName, $stage, $callable);
    }
}
