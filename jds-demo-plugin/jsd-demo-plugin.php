<?php
/** @noinspection PhpUndefinedFunctionInspection */

/**
 * Plugin Name: JDS Demo Plugin
 * Plugin URI:        https://jeremiahsturgill.com
 * Description:       Demo WordPress plugin
 * Version:           0.1.0
 * Requires at least: 5.7
 * Requires PHP:      7.4
 * Author:            Jeremiah Sturgill
 * Author URI:        https://jeremiahsturgill.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        jds-demo-plugin
 * Text Domain:       jds-demo-plugin-domain
 * Domain Path:       /languages
 */

namespace JdsDemoPlugin;


use Exception;
use JdsDemoPlugin\Services\DependencyContainer;

define("JdsDemoPlugin\ROOT_PLUGIN_DIR", plugin_dir_path(__FILE__));

/** @noinspection PhpIncludeInspection */
require ROOT_PLUGIN_DIR . 'vendor/autoload.php';

try {
	$di = DependencyContainer::create(ROOT_PLUGIN_DIR);

	/** @var Plugin $plugin */
	/** @noinspection PhpUnusedLocalVariableInspection */
	$plugin = $di->get(Plugin::class);
} catch (Exception $e) {
	error_log("jds-demo-plugin failed to initialize: {$e->getMessage()}");
}
