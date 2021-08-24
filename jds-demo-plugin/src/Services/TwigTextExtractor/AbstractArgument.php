<?php

namespace JdsDemoPlugin\Services\TwigTextExtractor;

use JdsDemoPlugin\Exceptions\CommandFailureException;
use JdsDemoPlugin\Exceptions\InvalidArgumentException;
use Twig\Node\Expression\Binary\ConcatBinary;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Node\Node;

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

	/**
	 * @throws InvalidArgumentException
	 */
	public
	static function ofNode(Node $node): IArgument
	{
		$method = 'of' . (new \ReflectionClass($node))->getShortName();

		if (!method_exists(__CLASS__, $method)) {
			throw new InvalidArgumentException("Unknown node type:" . get_class($node));
		}

		return self::$method($node);
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public
	static function ofConcatBinary(ConcatBinary $node): IArgument
	{
		// TODO -- fix this
		$name = 'placeholder';

		return new Argument($name, ArgumentRepresentations::VARIABLE);
	}
}
