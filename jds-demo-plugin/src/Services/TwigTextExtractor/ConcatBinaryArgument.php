<?php

/** @noinspection PhpUnused */

namespace JdsDemoPlugin\Services\TwigTextExtractor;

use Twig\Node\Expression\Binary\ConcatBinary;

class ConcatBinaryArgument extends AbstractArgument implements IArgument
{
    private IArgument $left;
    private IArgument $right;

    public function __construct(ConcatBinary $node, ArgumentFactory $argumentFactory)
    {
        $this->left = $argumentFactory->ofNode($node->getNode('left'));
        $this->right = $argumentFactory->ofNode($node->getNode('right'));
    }

    public function asSingleLineComment(?string $prefix = null): string
    {
        $right = ltrim($this->right->asSingleLineComment(), '/');
        return $this->stringToComment($this->left->asSingleLineComment() . $right, $prefix);
    }

    public function asPhpCode(): string
    {
        return $this->left->asPhpCode() . ' . ' . $this->right->asPhpCode();
    }
}
