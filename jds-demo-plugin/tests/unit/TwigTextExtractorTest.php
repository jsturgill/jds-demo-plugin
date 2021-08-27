<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace JdsDemoPlugin\Tests;

use Codeception\Test\Unit;
use JdsDemoPlugin\Services\TwigTextExtractor;
use Psr\Container\ContainerInterface;
use SplFileInfo;
use Twig\Error\SyntaxError;
use UnitTester;

class TwigTextExtractorTest extends Unit
{
    protected UnitTester $tester;

    protected ContainerInterface $di;
    protected ?TwigTextExtractor $twigTextExtractor = null;

    public const TEMPLATE_SUFFIX = '-function-template';

    public const GOLDEN_MASTERS_NAMESPACE = 'TwigTextExtractor';
    public const TEMPLATES_PATH_PARTIAL = 'templates';
    public const OUTPUT_PATH_PARTIAL = 'cache/gettext';

    public const TEMPLATE_EXTENSION = 'twig';
    public const OUTPUT_EXTENSION = 'php';


    protected function _before()
    {
        if (null === $this->twigTextExtractor) {
            $this->twigTextExtractor = $this->tester->getDiContainer()->get(TwigTextExtractor::class);
        }
    }

    protected function _after()
    {
    }

    private function getOutputFileContents($fileName): string
    {
        return $this->tester->getTestFileContents(self::OUTPUT_PATH_PARTIAL
            . DIRECTORY_SEPARATOR
            . $fileName);
    }

    private function getTemplatePath(string $fileNamePartial): string
    {
        return $this->tester->getTestFilesRoot()
            . DIRECTORY_SEPARATOR
            . self::TEMPLATES_PATH_PARTIAL
            . DIRECTORY_SEPARATOR
            . $fileNamePartial . '.' . self::TEMPLATE_EXTENSION;
    }

    /**
     * @throws SyntaxError
     */
    private function testTemplateAgainstMaster(string $fileNamePartial)
    {
        $file = new SplFileInfo($this->getTemplatePath($fileNamePartial));
        $this->twigTextExtractor->processTwigTemplate($file);
        $output = $this->getOutputFileContents($fileNamePartial . '.' . self::OUTPUT_EXTENSION);
        $goldenMaster = $this->tester->getGoldenMasterContents(self::GOLDEN_MASTERS_NAMESPACE . DIRECTORY_SEPARATOR . $fileNamePartial . '.txt');
        self::assertEquals($goldenMaster, $output);
    }

    /**
     * @throws SyntaxError
     */
    public function test__Template()
    {
        $this->testTemplateAgainstMaster('__' . self::TEMPLATE_SUFFIX);
    }

    /**
     * @throws SyntaxError
     */
    public function test_eTemplate()
    {
        $this->testTemplateAgainstMaster('_e' . self::TEMPLATE_SUFFIX);
    }

    /**
     * @throws SyntaxError
     */
    public function test_xTemplate()
    {
        $this->testTemplateAgainstMaster('_x' . self::TEMPLATE_SUFFIX);
    }

    /**
     * @throws SyntaxError
     */
    public function test_nTemplate()
    {
        $this->testTemplateAgainstMaster('_n' . self::TEMPLATE_SUFFIX);
    }

    /**
     * @throws SyntaxError
     */
    public function test_nxTemplate()
    {
        $this->testTemplateAgainstMaster('_nx' . self::TEMPLATE_SUFFIX);
    }

    /**
     * @throws SyntaxError
     */
    public function test_n_noopTemplate()
    {
        $this->testTemplateAgainstMaster('_n_noop' . self::TEMPLATE_SUFFIX);
    }

    /**
     * @throws SyntaxError
     */
    public function test_nx_noopTemplate()
    {
        $this->testTemplateAgainstMaster('_nx_noop' . self::TEMPLATE_SUFFIX);
    }

    /**
     * @throws SyntaxError
     */
    public function test_ex_noopTemplate()
    {
        $this->testTemplateAgainstMaster('_ex' . self::TEMPLATE_SUFFIX);
    }
}
