<?php

namespace JdsDemoPlugin\Services\TwigTextExtractor;

use JdsDemoPlugin\Exceptions\InvalidArgumentException;

abstract class AbstractArgument implements IArgument
{
	/**
	 * @link https://www.php.net/manual/en/language.variables.basics.php Source for valid PHP variable regex
	 */
	const VALID_PHP_VAR_NAME_REGEX = '/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/';

	/**
	 * Returns a string guaranteed not to contain line breaks or `?>`
	 *
	 * Ensures the string starts with the necessary translators prefix,
	 * and adds `// ` to the start.
	 *
	 * @return ?string
	 **/
	protected function stringToComment(string $value, ?string $prefix = null): string
	{
		$intermediate = str_replace(["\r", "\n", '?>'], " ", $value);

		if (null !== $prefix && !str_starts_with($intermediate, $prefix)) {
			$intermediate = $prefix . $intermediate;
		}

		return "// $intermediate";
	}

	/**
	 * @throws InvalidArgumentException
	 */
	protected function stringToVariable(string $value): string
	{
		if (!preg_match(self::VALID_PHP_VAR_NAME_REGEX, $value)) {
			throw new InvalidArgumentException("Invalid PHP variable name: '$value'");
		}
		return '$' . $value;
	}

	protected function stringToPhpString(string $value): string
	{
		return '"' . addslashes($value) . '"';
	}
}
