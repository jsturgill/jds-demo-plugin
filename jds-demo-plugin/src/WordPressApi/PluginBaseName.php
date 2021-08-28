<?php

namespace JdsDemoPlugin\WordPressApi;

class PluginBaseName
{
    private string $pluginFilePath;
    private string $value;
    public function __construct(string $pluginFilePath)
    {
        $this->pluginFilePath = $pluginFilePath;
        $this->value = plugin_basename($this->pluginFilePath);
    }

    public function toString(): string
    {
        return (string)$this;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
