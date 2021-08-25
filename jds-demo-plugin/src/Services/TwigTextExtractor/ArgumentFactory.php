<?php

namespace JdsDemoPlugin\Services\TwigTextExtractor;

use JdsDemoPlugin\Exceptions\InvalidArgumentException;
use ReflectionClass;
use Twig\Node\Node;

class ArgumentFactory
{
	/**
	 * @throws InvalidArgumentException
	 */
	public function ofNode(Node $node): IArgument
	{
		$class = __NAMESPACE__ . '\\' . (new ReflectionClass($node))->getShortName() . 'Argument';
		$class = new $class($node, $this);
		if (false === $class instanceof IArgument) {
			throw new InvalidArgumentException("Invalid node type");
		}
		return $class;
	}
}
