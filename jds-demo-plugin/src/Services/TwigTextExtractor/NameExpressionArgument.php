<?php /** @noinspection PhpUnused */

namespace JdsDemoPlugin\Services\TwigTextExtractor;

use JdsDemoPlugin\Exceptions\InvalidArgumentException;
use Twig\Node\Expression\NameExpression;
use Twig\Node\Node;

class NameExpressionArgument extends AbstractArgument implements IArgument
{

	private string $value;

	/**
	 * @throws InvalidArgumentException
	 */
	public function __construct(NameExpression $node)
	{
		$this->value = $node->getAttribute('name');
		if (!preg_match(AbstractArgument::VALID_PHP_VAR_NAME_REGEX, $this->value)) {
			throw new InvalidArgumentException("Invalid PHP variable name: '$this->value'");
		}
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function asSingleLineComment(?string $prefix = null): string
	{
		return $this->stringToComment($this->asPhpCode(), $prefix);
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function asPhpCode(): string
	{
		return $this->stringToVariable($this->value);
	}
}
