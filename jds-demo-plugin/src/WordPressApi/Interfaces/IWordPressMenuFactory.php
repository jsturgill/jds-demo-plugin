<?php

namespace JdsDemoPlugin\WordPressApi\Interfaces;

use JdsDemoPlugin\WordPressApi\WordPressMenu;

interface IWordPressMenuFactory {

	/**
	 * @param string $parentSlug
	 * @param string $pageTitle
	 * @param string $menuTitle
	 * @param string $capability
	 * @param string $menuSlug
	 * @param callable $renderFunction
	 * @param int|null $position
	 *
	 * @return WordPressMenu
	 */
	public function createMenuWithRenderCallback(
		string $parentSlug,
		string $pageTitle,
		string $menuTitle,
		string $capability,
		string $menuSlug,
		callable $renderFunction,
		int $position = null
	): WordPressMenu;

	/**
	 * @param string $parentSlug
	 * @param string $pageTitle
	 * @param string $menuTitle
	 * @param string $capability
	 * @param string $menuSlug
	 * @param string $templateName
	 * @param callable $environmentFactory returns an array to be used as the template context
	 * @param int|null $position
	 *
	 * @return WordPressMenu
	 */
	public function createMenuWithTemplate(
		string $parentSlug,
		string $pageTitle,
		string $menuTitle,
		string $capability,
		string $menuSlug,
		string $templateName,
		callable $environmentFactory,
		int $position = null
	): WordPressMenu;
}
