<?php
namespace JdsDemoPlugin\Config;

class TemplateConfig {
	public string $templateRootPath;
	public string $templateCachePath;

	public function __construct( string $templateRootPath, string $templateCachePath ) {
		$this->templateRootPath  = $templateRootPath;
		$this->templateCachePath = $templateCachePath;
	}
}
