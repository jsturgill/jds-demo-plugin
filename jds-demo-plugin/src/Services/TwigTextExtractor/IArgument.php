<?php

namespace JdsDemoPlugin\Services\TwigTextExtractor;

use JdsDemoPlugin\Exceptions\CommandFailureException;

/**
 * Represents an argument to a function in Twig
 *
 * Implementations should support a constructor that accept a single Node object parameter.
 *
 * @see ArgumentFactory
 */
interface IArgument
{
	/**
	 * Returns a string guaranteed not to contain line breaks or `?>`
	 *
	 * Ensures the string starts with the necessary translators prefix,
	 * and adds `// ` to the start.
	 *
	 * @param string|null $prefix
	 * @return string
	 */
	public function asSingleLineComment(?string $prefix = null): string;

	/**
	 * @return float|int|string
	 * @throws CommandFailureException
	 */
	public function asPhpCode();

}
