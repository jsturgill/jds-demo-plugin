<?php

namespace JdsDemoPlugin\WordPressApi;

use JdsDemoPlugin\WordPressApi\Menu;

interface IMenuFactory
{
    /**
     * @param string $parentSlug
     * @param string $pageTitle
     * @param string $menuTitle
     * @param string $capability
     * @param string $menuSlug
     * @param callable():void $renderFunction
     * @param int|null $position
     *
     * @return Menu
     */
    public function createMenuWithRenderCallback(
        string   $parentSlug,
        string   $pageTitle,
        string   $menuTitle,
        string   $capability,
        string   $menuSlug,
        callable $renderFunction,
        int      $position = null
    ): Menu;

    /**
     * @param string $parentSlug
     * @param string $pageTitle
     * @param string $menuTitle
     * @param string $capability
     * @param string $menuSlug
     * @param string $templateName
     * @param callable():array<string,mixed> $environmentFactory returns an array to be used as the template context
     * @param int|null $position
     *
     * @return Menu
     */
    public function createMenuWithTemplate(
        string   $parentSlug,
        string   $pageTitle,
        string   $menuTitle,
        string   $capability,
        string   $menuSlug,
        string   $templateName,
        callable $environmentFactory,
        int      $position = null
    ): Menu;
}
