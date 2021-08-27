<?php

namespace JdsDemoPlugin\WordPressApi;

interface IPluginLifecycleActionFactory
{
    public function createAction(string $pluginBaseName, string $stage, callable $callable): PluginLifecycleAction;
}
