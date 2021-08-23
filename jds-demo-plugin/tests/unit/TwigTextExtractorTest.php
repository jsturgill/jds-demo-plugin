<?php

namespace JdsDemoPlugin\Tests;

use JdsDemoPlugin\Services\DependencyContainer;
use JdsDemoPlugin\Services\TwigTextExtractor;
use Psr\Container\ContainerInterface;
use SplFileInfo;
use Twig\Error\SyntaxError;
use UnitTester;

class TwigTextExtractorTest extends \Codeception\Test\Unit {

	protected UnitTester $tester;

	protected ContainerInterface $di;
	protected TwigTextExtractor $instance;

	const TEMPLATES_PATH_PARTIAL = TEST_FILES_ROOT . "/templates";
	const OUTPUT_PATH_PARTIAL = TEST_FILES_ROOT . "/cache/gettext";
	const __TEST_TEMPLATE = "__-function-template";
	const _E_TEST_TEMPLATE = '_e-function-template';

	private function getGoldenMasterContents( string $fileName ): string {
		return file_get_contents( GOLDEN_MASTERS_ROOT . "/TwigTextExtractor/$fileName." . GOLDEN_MASTERS_EXTENSION );
	}

	private function templatePath( string $fileName ): string {
		return self::TEMPLATES_PATH_PARTIAL . "/$fileName.twig";
	}

	private function outputPath( string $fileName ): string {
		return self::OUTPUT_PATH_PARTIAL . "/$fileName.php";
	}

	/**
	 * @throws \Exception
	 */
	protected function _before() {
		$this->di       = DependencyContainer::create( TEST_FILES_ROOT );
		$this->instance = $this->di->get( TwigTextExtractor::class );
	}

	protected function _after() {
	}

	/**
	 * @throws SyntaxError
	 */
	private function testTemplateAgainstMaster( $fileName ) {
		$file = new SplFileInfo( $this->templatePath( $fileName ) );
		$this->instance->processTwigTemplate( $file );
		$output       = file_get_contents( $this->outputPath( $fileName ) );
		$goldenMaster = $this->getGoldenMasterContents( $fileName );
		self::assertEquals( $goldenMaster, $output );
	}

	/**
	 * @throws SyntaxError
	 */
	public function test__Template() {
		$this->testTemplateAgainstMaster( self::__TEST_TEMPLATE );
	}

	public function test_eTemplate() {
		$this->testTemplateAgainstMaster( self::_E_TEST_TEMPLATE );
	}
}
