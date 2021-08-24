<?php /** @noinspection PhpUnused */

namespace JdsDemoPlugin\Services\TwigTextExtractor;

use Twig\Node\Expression\ConstantExpression;

class ConstantExpressionArgument extends AbstractArgument implements IArgument
{

	/**
	 * @var string|int|float
	 */
	private $value;

	public function __construct(ConstantExpression $node)
	{
		$this->value = $node->getAttribute('value');
	}

	public function asSingleLineComment(?string $prefix = null): string
	{
		return $this->stringToComment($this->value, $prefix);
	}

	public function asPhpCode(): string
	{
		if (is_int($this->value) || is_float($this->value)) {
			return $this->value;
		}

		return $this->stringToPhpString($this->value);
	}
}
