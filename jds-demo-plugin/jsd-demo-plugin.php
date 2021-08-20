<?php
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
 * Text Domain:       jds-demo-plugin
 * Domain Path:       /languages
 */

namespace JdsDemoPlugin;

require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

$plugin = new JdsDemoPlugin();
$plugin->init();
