<?php

namespace JdsDemoPlugin;

use JdsDemoPlugin\WordPressApi\Interfaces\IWordPressMenuFactory;
use JdsDemoPlugin\WordPressApi\WordPressMenu;

class Plugin
{
    public const TRANSLATION_DOMAIN = 'jds-demo-plugin-domain';
    public const TEMPLATE_OPTIONS_MENU = 'jds-demo-plugin-options.twig';
    private WordPressMenu $optionsMenu;

    public const NAME_BANK = ['You', 'Person', 'World', 'Dolly'];

    public function __construct(IWordPressMenuFactory $menuFactory)
    {
        $this->optionsMenu = $menuFactory->createMenuWithTemplate(
            "options-general.php",
            __("JDS Demo Plugin", "jds-demo-plugin-domain"),
            __("JDS Demo Plugin", "jds-demo-plugin-domain"),
            "manage_options",
            "jds-demo-plugin-options",
            Plugin::TEMPLATE_OPTIONS_MENU,
            fn () => ['audience' => self::NAME_BANK[array_rand(self::NAME_BANK)]]
        );
    }

    /** @noinspection PhpUnused */
    public function getOptionsMenu(): WordPressMenu
    {
        return $this->optionsMenu;
    }
}
