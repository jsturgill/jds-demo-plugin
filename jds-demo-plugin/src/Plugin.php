<?php

namespace JdsDemoPlugin;

use JdsDemoPlugin\WordPressApi\Interfaces\IWordPressMenuFactory;
use JdsDemoPlugin\WordPressApi\WordPressMenu;
use JdsDemoPlugin\WordPressApi\WordPressMenuFactory;

class Plugin {

	const TEMPLATE_OPTIONS_MENU = 'jds-demo-plugin-options.twig';
	private WordPressMenu $optionsMenu;

	public function __construct( IWordPressMenuFactory $menuFactory ) {
		$this->optionsMenu = $menuFactory->createMenuWithTemplate( "options-general.php",
			"JDS Demo Plugin",
			"JDS Demo Plugin",
			"manage_options",
			"jds-demo-plugin-options",
			$this::TEMPLATE_OPTIONS_MENU,
			[ WordPressMenuFactory::class, 'emptyEnvironmentFactory' ]
		);
	}

	public function getOptionsMenu(): WordPressMenu {
		return $this->optionsMenu;
	}
}
