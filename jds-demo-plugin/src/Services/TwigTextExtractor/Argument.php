<?php

namespace JdsDemoPlugin\Services\TwigTextExtractor;

use JdsDemoPlugin\Exceptions\CommandFailureException;
use JdsDemoPlugin\Exceptions\InvalidArgumentException;
use Twig\Node\Expression\Binary\ConcatBinary;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Node\Node;

class Argument
{

	public int $representation;
	public string $stringValue;
	const VALID_PHP_VAR_NAME_REGEX = '/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/';

	/**
	 * @throws InvalidArgumentException
	 */
	public function __construct(string $stringValue, int $representation)
	{
		ArgumentRepresentations::assertIsValid($representation);

		$this->representation = $representation;
		$this->stringValue = $stringValue;
	}

	/**
	 * Returns a string guaranteed not to contain line breaks or `?>`
	 *
	 * Ensures the string starts with the necessary translators prefix,
	 * and adds `// ` to the start.
	 *
	 * @return ?string
	 **/
	public function asComment(?string $prefix = null): string
	{
		$intermediate = str_replace(["\r", "\n", '?>'], " ", $this->stringValue);

		if (null !== $prefix && !str_starts_with($intermediate, $prefix)) {
			$intermediate = $prefix . $intermediate;
		}

		return "// $intermediate";
	}

	/**
	 * @throws CommandFailureException
	 */
	public function asPhpCode(): string
	{
		if (ArgumentRepresentations::STRING === $this->representation) {
			return '"' . addslashes($this->stringValue) . '"';
		}
		if (ArgumentRepresentations::VARIABLE) {
			return '$' . $this->stringValue;
		}
		throw new CommandFailureException("Not implemented");
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public static function ofNode(Node $node): Argument
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
	public static function ofConstantExpression(ConstantExpression $node): Argument
	{
		return new Argument($node->getAttribute('value'), ArgumentRepresentations::STRING);
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public static function ofNameExpression(NameExpression $node): Argument
	{
		$name = $node->getAttribute('name');
		if (!preg_match(self::VALID_PHP_VAR_NAME_REGEX, $name)) {
			throw new InvalidArgumentException("Invalid PHP variable name: '$name'");
		}

		return new Argument($name, ArgumentRepresentations::VARIABLE);
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public static function ofConcatBinary(ConcatBinary $node): Argument
	{
		// TODO -- fix this
		$name = 'placeholder';

		return new Argument($name, ArgumentRepresentations::VARIABLE);
	}
}
