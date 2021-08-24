<?php /** @noinspection PhpUnused */

namespace JdsDemoPlugin\Tests;

use Codeception\Test\Unit;
use Exception;
use JdsDemoPlugin\Services\DependencyContainerFactory;
use JdsDemoPlugin\Services\TwigTextExtractor;
use Psr\Container\ContainerInterface;
use SplFileInfo;
use Twig\Error\SyntaxError;
use UnitTester;

class TwigTextExtractorTest extends Unit
{

	protected UnitTester $tester;

	protected ContainerInterface $di;
	protected TwigTextExtractor $instance;

	const TEMPLATES_PATH_PARTIAL = TEST_FILES_ROOT . "/templates";
	const OUTPUT_PATH_PARTIAL = TEST_FILES_ROOT . "/cache/gettext";
	const TEMPLATE_SUFFIX = '-function-template';

	private function getGoldenMasterContents(string $fileName): string
	{
		return file_get_contents(GOLDEN_MASTERS_ROOT . "/TwigTextExtractor/$fileName." . GOLDEN_MASTERS_EXTENSION);
	}

	private function templatePath(string $fileName): string
	{
		return self::TEMPLATES_PATH_PARTIAL . "/$fileName.twig";
	}

	private function outputPath(string $fileName): string
	{
		return self::OUTPUT_PATH_PARTIAL . "/$fileName.php";
	}

	/**
	 * @throws Exception
	 */
	protected function _before()
	{
		$this->di = (new DependencyContainerFactory)->create(TEST_FILES_ROOT, DependencyContainerFactory::ENV_TEST);
		$this->instance = $this->di->get(TwigTextExtractor::class);
	}

	protected function _after()
	{
	}

	/**
	 * @throws SyntaxError
	 */
	private function testTemplateAgainstMaster($fileName)
	{
		$file = new SplFileInfo($this->templatePath($fileName));
		$this->instance->processTwigTemplate($file);
		$output = file_get_contents($this->outputPath($fileName));
		$goldenMaster = $this->getGoldenMasterContents($fileName);
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
}
