<?php

namespace JdsDemoPlugin\Services\TwigTextExtractor;

use JdsDemoPlugin\Exceptions\InvalidArgumentException;
use Twig\Node\Expression\Binary\ConcatBinary;

class ConcatBinaryArgument extends AbstractArgument implements IArgument
{

	private ConcatBinary $node;

	public function __construct(ConcatBinary $node, ArgumentFactory $argumentFactory)
	{
		$this->node = $node;
	}

	public function asComment(?string $prefix = null): string
	{
		return $this->stringToComment('placeholder comment', $prefix);
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function asPhpCode(): string
	{
		return $this->stringToVariable('placeholderVarName');
	}
}
