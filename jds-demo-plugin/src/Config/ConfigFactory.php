<?php

namespace JdsDemoPlugin\Config;

use JdsDemoPlugin\Config\TemplateConfig;

class ConfigFactory {
	const PATH_PARTIAL_TEMPLATES = "templates";
	const PATH_PARTIAL_CACHE_ROOT = "cache";
	const PATH_PARTIAL_TEMPLATE_CACHE = ConfigFactory::PATH_PARTIAL_CACHE_ROOT . "/templates";
	const PATH_PARTIAL_DI_CACHE = ConfigFactory::PATH_PARTIAL_CACHE_ROOT . "/di";
	protected string $pluginRootPath;

	public function __construct( string $rootPluginPath ) {
		$trailingChar = substr( $rootPluginPath, - 1 );
		if ( $trailingChar !== DIRECTORY_SEPARATOR && $trailingChar !== '/' ) {
			$rootPluginPath = $rootPluginPath . '/';
		}
		$this->pluginRootPath = $rootPluginPath;
	}

	public function createTemplateConfig(): TemplateConfig {
		return new TemplateConfig( $this->pluginRootPath . $this::PATH_PARTIAL_TEMPLATES,
			$this->pluginRootPath . $this::PATH_PARTIAL_TEMPLATE_CACHE );
	}
}
