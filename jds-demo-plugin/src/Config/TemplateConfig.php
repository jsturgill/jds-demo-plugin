<?php

namespace JdsDemoPlugin\Config;

use JdsDemoPlugin\Services\FileSystem;

class TemplateConfig {
	public string $templateRootPath;
	public string $templateCachePath;

	public function __construct( string $templateRootPath, string $templateCachePath ) {
		$this->templateRootPath  = rtrim( $templateRootPath, FileSystem::PATH_SEPARATORS );
		$this->templateCachePath = rtrim( $templateCachePath, FileSystem::PATH_SEPARATORS );
	}
}
