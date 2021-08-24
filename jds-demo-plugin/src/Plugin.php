<?php

namespace JdsDemoPlugin;

use JdsDemoPlugin\WordPressApi\Interfaces\IWordPressMenuFactory;
use JdsDemoPlugin\WordPressApi\WordPressMenu;

class Plugin
{
	const TEMPLATE_OPTIONS_MENU = 'jds-demo-plugin-options.twig';
	private WordPressMenu $optionsMenu;

	const NAME_BANK = ['You', 'Person', 'World', 'Dolly'];

	public function __construct(IWordPressMenuFactory $menuFactory)
	{
		$this->optionsMenu = $menuFactory->createMenuWithTemplate("options-general.php",
			__("JDS Demo Plugin", "jds-demo-plugin-domain"),
			__("JDS Demo Plugin", "jds-demo-plugin-domain"),
			"manage_options",
			"jds-demo-plugin-options",
			$this::TEMPLATE_OPTIONS_MENU,
			fn() => ['audience' => self::NAME_BANK[array_rand(self::NAME_BANK)]]
		);
	}

	/** @noinspection PhpUnused */
	public function getOptionsMenu(): WordPressMenu
	{
		return $this->optionsMenu;
	}
}
