<?php

namespace JdsDemoPlugin\Services\TwigTextExtractor;

use Twig\Node\Expression\ConstantExpression;

class ConstantExpressionArgument extends AbstractArgument implements IArgument
{

	private string $value;

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
		return $this->stringToPhpString($this->value);
	}
}
