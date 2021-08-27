<?php

namespace JdsDemoPlugin\WordPressApi;

use JdsDemoPlugin\Exceptions\InvalidArgumentException;
use JdsDemoPlugin\WordPressApi\IMenuFactory;
use Twig\Environment;

class MenuFactory implements IMenuFactory
{
    private Environment $twig;

    /**
     * @return array<string,mixed>
     */
    public static function emptyEnvironmentFactory(): array
    {
        return [];
    }

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function createMenuWithRenderCallback(
        string   $parentSlug,
        string   $pageTitle,
        string   $menuTitle,
        string   $capability,
        string   $menuSlug,
        callable $renderFunction,
        int      $position = null
    ): Menu {
        return new Menu(
            $parentSlug,
            $pageTitle,
            $menuTitle,
            $capability,
            $menuSlug,
            $renderFunction,
            $position
        );
    }

    /**
     * @param string $parentSlug
     * @param string $pageTitle
     * @param string $menuTitle
     * @param string $capability
     * @param string $menuSlug
     * @param string $templateName
     * @param callable():array<string, mixed> $environmentFactory
     * @param int|null $position
     * @return Menu
     * @throws InvalidArgumentException
     */
    public function createMenuWithTemplate(
        string   $parentSlug,
        string   $pageTitle,
        string   $menuTitle,
        string   $capability,
        string   $menuSlug,
        string   $templateName,
        callable $environmentFactory,
        int      $position = null
    ): Menu {
        if (!is_callable($environmentFactory)) {
            throw new InvalidArgumentException('The $environmentFactory argument must be callable');
        }

        return $this->createMenuWithRenderCallback(
            $parentSlug,
            $pageTitle,
            $menuTitle,
            $capability,
            $menuSlug,
            function () use ($templateName, $environmentFactory) {
                echo $this->twig->render($templateName, call_user_func($environmentFactory));
            },
            $position
        );
    }
}
