<?php

namespace JdsDemoPlugin;

class JdsDemoPlugin {
	public function init(): void {
		add_action( "admin_menu", [ $this, "registerMenu" ] );
	}

	/**
	 * Register the options menu
	 */
	public function registerMenu(): void {
		// https://developer.wordpress.org/reference/functions/add_submenu_page/
		add_submenu_page( "options-general.php",
			"JDS Demo Plugin",
			"JDS Demo Plugin",
			"manage_options",
			"jds-demo-plugin",
			[ $this, "renderPage" ] );
	}

	public function renderPage(): void {
		echo <<<PAGE
<h1>Demo Plugin Page</h1>
<p>Hello, world!</p>
<h2>TODO</h2>
<ul>
	<li>Use templates instead of this</li>
</ul>
PAGE;

	}
}
