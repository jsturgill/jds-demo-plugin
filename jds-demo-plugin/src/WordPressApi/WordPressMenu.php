<?php

namespace JdsDemoPlugin\WordPressApi;

use JdsDemoPlugin\Exceptions\InvalidArgumentException;

class WordPressMenu {
	public string $parentSlug;
	public string $pageTitle;
	public string $menuTitle;
	public string $capability;
	public string $menuSlug;
	public ?int $position = null;
	/**
	 * @var callable $renderFunction
	 */
	public $renderFunction;
	/**
	 * @var string|bool|null $value null initially; string if successfully created; false if failed to create (user does not have the required capability)
	 */
	public $value;

	/**
	 * @throws InvalidArgumentException
	 */
	public function __construct(
		string $parentSlug,
		string $pageTitle,
		string $menuTitle,
		string $capability,
		string $menuSlug,
		callable $renderFunction,
		int $position = null
	) {
		if ( ! is_callable( $renderFunction ) ) {
			throw new InvalidArgumentException( 'The $renderFunction argument must be a valid callback' );
		}
		$this->parentSlug     = $parentSlug;
		$this->pageTitle      = $pageTitle;
		$this->menuTitle      = $menuTitle;
		$this->capability     = $capability;
		$this->menuSlug       = $menuSlug;
		$this->renderFunction = $renderFunction;
		$this->position       = $position;

		/** @noinspection PhpUndefinedFunctionInspection */
		add_action( "admin_menu", function () {
			/** @noinspection PhpUndefinedFunctionInspection */
			$result      = add_submenu_page( $this->parentSlug,
				$this->pageTitle,
				$this->menuTitle,
				$this->capability,
				$this->menuSlug,
				$this->renderFunction,
				$this->position
			);
			$this->value = $result;
		} );
	}

	public function isCreated(): bool {
		return is_string( $this->value );
	}

}
