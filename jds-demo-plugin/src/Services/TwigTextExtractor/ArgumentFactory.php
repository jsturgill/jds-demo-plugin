<?php

namespace JdsDemoPlugin\Services\TwigTextExtractor;

use ReflectionClass;
use Twig\Node\Node;

class ArgumentFactory
{
	public function ofNode(Node $node): IArgument
	{
		$class = __NAMESPACE__ . '\\' . (new ReflectionClass($node))->getShortName() . 'Argument';
		return new $class($node, $this);
	}
}
