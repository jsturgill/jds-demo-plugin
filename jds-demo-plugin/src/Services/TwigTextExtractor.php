<?php

namespace JdsDemoPlugin\Services;

use Exception;
use JdsDemoPlugin\Exceptions\CommandFailureException;
use JdsDemoPlugin\Exceptions\InvalidArgumentException;
use JdsDemoPlugin\Services\TwigTextExtractor\ArgumentFactory;
use JdsDemoPlugin\Services\TwigTextExtractor\IArgument;
use JdsDemoPlugin\Services\TwigTextExtractor\TwigTextExtractorConfig;
use SplFileInfo;
use Twig\Environment;
use Twig\Error\SyntaxError;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Node;
use Twig\Source;

class TwigTextExtractor
{
    public const TRANSLATOR_COMMENT_PREFIX = 'translators: ';

    private TwigTextExtractorConfig $config;
    private Environment $twig;
    private FileSystem $fileSystem;
    private ArgumentFactory $argumentFactory;
    private string $cleanDomain;


    public const FUNCTIONS_TO_PARAM_COUNT_MAP = [
        '__' => 1,
        '_e' => 1,
        '_x' => 2,
        '_ex' => 2,
        '_n' => 3,
        '_nx' => 4,
        '_n_noop' => 2,
        '_nx_noop' => 3,
    ];

    /**
     * @throws Exception
     */
    public function __construct(
        TwigTextExtractorConfig $config,
        Environment             $twig,
        FileSystem              $fileSystem,
        ArgumentFactory         $argumentFactory
    ) {
        $this->config = $config;
        $this->twig = $twig;
        $this->fileSystem = $fileSystem;
        $this->argumentFactory = $argumentFactory;
        $this->cleanDomain = addslashes($config->domain());

        if ($this->cleanDomain !== $config->domain()) {
            throw new Exception("domain value is terribly wrong (should resemble a slug): {$config->domain()}");
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws CommandFailureException
     */
    public function clearExtractions(): void
    {
        $this->fileSystem->emptyDirectory(
            $this->config->outputDir,
            [FileSystem::class, 'deleteAllButGitignore']
        );
    }

    /**
     * @return IArgument[]
     * @throws Exception
     */
    private function extractArguments(Node $node, int $min = 1): array
    {
        if (!$node->hasNode('arguments')) {
            throw new InvalidArgumentException("Cannot extract arguments from a node that does not have any");
        }

        $result = [];

        /** @var Node $argument */
        foreach ($node->getNode('arguments')->getIterator() as $argument) {
            array_push($result, $this->argumentFactory->ofNode($argument));
        }

        if (count($result) < $min) {
            throw new InvalidArgumentException("FunctionExpression node has " . count($result) . "arguments (minimum: $min)");
        }

        return $result;
    }

    /**
     * @param IArgument[] $translationFuncArgs
     * @param string $translationFuncName
     * @param int $translationFuncParamsCount do not include the domain parameter, which will always be included
     * @param bool $wrapInSprintf
     * @param IArgument[]|null $sprintfArgs
     *
     * @return string
     * @throws CommandFailureException|InvalidArgumentException
     * @see TwigTextExtractor::FUNCTIONS_TO_PARAM_COUNT_MAP
     */
    private function codeGenerator(
        array  $translationFuncArgs,
        string $translationFuncName,
        int    $translationFuncParamsCount = 1,
        bool   $wrapInSprintf = false,
        ?array $sprintfArgs = null
    ): string {
        if ($translationFuncParamsCount < 1) {
            throw new InvalidArgumentException("Each translation function requires at least 1 argument");
        }
        // 0-based index means the param count is also the index of the optional comment parameter
        $comment = array_key_exists($translationFuncParamsCount, $translationFuncArgs) ? $translationFuncArgs[$translationFuncParamsCount] : null;
        $cleanComment = null !== $comment
            ? $comment->asSingleLineComment(self::TRANSLATOR_COMMENT_PREFIX)
            : null;

        // reduce the arguments array to the expected length by dropping items
        // from the tail end
        while (count($translationFuncArgs) > $translationFuncParamsCount) {
            array_pop($translationFuncArgs);
        }

        // escape, quote, separate with commas, and append the domain
        $cleanTranslationFuncArgs = join(
            ", ",
            array_map(fn (IArgument $arg) => $arg->asPhpCode(), $translationFuncArgs)
        ) . ", \"$this->cleanDomain\"";

        $cleanSprintfArgs = !is_array($sprintfArgs)
            ? null
            : join(
                ", ",
                array_map(
                    fn (IArgument $arg) => $arg->asPhpCode(),
                    $sprintfArgs
                )
            );

        $prefix = null === $cleanComment
            ? ""
            : "$cleanComment\n";

        if ($wrapInSprintf) {
            return $prefix . "sprintf( $translationFuncName( $cleanTranslationFuncArgs ), $cleanSprintfArgs );";
        }

        return $prefix . "$translationFuncName( $cleanTranslationFuncArgs );";
    }

    /**
     * Processes a translation function expression in a twig template
     *
     * This is the simplest case.
     * @param FunctionExpression $node
     * @param array<string,string> $text
     * @throws CommandFailureException
     * @throws Exception
     * @see TwigTextExtractor::processFilterExpression the more complex "sprintf" style case
     */
    private function processFunctionExpression(FunctionExpression $node, array &$text): void
    {
        $functionName = (string)$node->getAttribute('name');

        if (!array_key_exists($functionName, self::FUNCTIONS_TO_PARAM_COUNT_MAP)) {
            return;
        }

        $arguments = $this->extractArguments($node, self::FUNCTIONS_TO_PARAM_COUNT_MAP[$functionName]);
        $textValue = (string)$arguments[0]->asPhpCode();

        // don't overwrite the value if it already exists
        if (array_key_exists($textValue, $text)) {
            return;
        }

        $text[$textValue] = $this->codeGenerator(
            $arguments,
            $functionName,
            self::FUNCTIONS_TO_PARAM_COUNT_MAP[$functionName]
        );
    }

    /**
     * @param FilterExpression $node
     * @param array<string,string> $text
     * @throws CommandFailureException|InvalidArgumentException
     * @throws Exception
     */
    private function processFilterExpression(FilterExpression $node, array &$text): void
    {
        // the filtered node should be a function expression where the
        // function name is one of the supported translation functions
        $filteredNode = $node->getNode('node');

        if (false === $filteredNode instanceof FunctionExpression) {
            return;
        }

        $functionName = (string)$filteredNode->getAttribute('name');

        if (!array_key_exists($functionName, self::FUNCTIONS_TO_PARAM_COUNT_MAP)) {
            return;
        }

        // ensure the filter being processed is the 'format' filter
        $filterNode = $node->getNode('filter');
        if ('format' !== $filterNode->getAttribute('value')) {
            return;
        }

        $translationArgs = $this->extractArguments($filteredNode, self::FUNCTIONS_TO_PARAM_COUNT_MAP[$functionName]);

        $formatArgs = $this->extractArguments($node);

        $textValue = $translationArgs[0]->asPhpCode();
        $text[(string)$textValue] = $this->codeGenerator(
            $translationArgs,
            $functionName,
            self::FUNCTIONS_TO_PARAM_COUNT_MAP[$functionName],
            true,
            $formatArgs
        );
    }

    /**
     * Visits each node in and extracts translated strings
     * @param Node $node
     * @param array<string,string> $text
     * @throws CommandFailureException
     * @throws Exception
     */
    private function processNode(Node $node, array &$text): void
    {
        // sprintf format
        if ($node instanceof FilterExpression) {
            $this->processFilterExpression($node, $text);
        }

        // simple strings
        if ($node instanceof FunctionExpression) {
            $this->processFunctionExpression($node, $text);
        }

        /** @var Node $childNode */
        foreach ($node->getIterator() as $childNode) {
            $this->processNode($childNode, $text);
        }
    }

    /**
     * @throws SyntaxError
     * @throws Exception
     */
    public function processTwigTemplate(SplFileInfo $fileInfo): void
    {
        if ('twig' !== $fileInfo->getExtension() || false === $fileInfo->getRealPath()) {
            return;
        }

        $fileContents = file_get_contents($fileInfo->getRealPath());

        if (false === $fileContents) {
            return;
        }

        // add one to input path to account for trailing slash
        $relativePath = mb_substr($fileInfo->getRealPath(), $this->config->inputPathLength + 1);

        $stream = $this->twig->tokenize(
            new Source(
                $fileContents,
                $relativePath,
                $fileInfo->getRealPath()
            )
        );
        $nodes = $this->twig->parse($stream);
        $text = [];

        /** @var Node $node */
        foreach ($nodes->getIterator() as $node) {
            $this->processNode($node, $text);
        }

        // filter out skip entries and join together as lines of code to write out
        $lines = join("\n", array_values($text));
        file_put_contents($this->config->toOutputFilePath($relativePath), "<?php\n$lines\n");
    }

    /**
     * @throws InvalidArgumentException
     */
    public function extractText(): void
    {
        $this->fileSystem->processFiles($this->config->inputDir, [$this, 'processTwigTemplate']);
    }
}
