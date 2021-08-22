<?php

namespace JdsDemoPlugin\Services;

use DI;
use Exception;
use http\Env;
use JdsDemoPlugin\Config\ConfigFactory;
use JdsDemoPlugin\Config\TemplateConfig;
use JdsDemoPlugin\Plugin;
use JdsDemoPlugin\WordPressApi\Interfaces\IWordPressMenuFactory;
use JdsDemoPlugin\WordPressApi\WordPressMenuFactory;
use Psr\Container\ContainerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

class DependencyContainer {
	/**
	 * @throws Exception
	 */
	public static function create( $rootPluginPath ): DI\Container {
		$containerBuilder = new DI\ContainerBuilder();
		$containerBuilder->enableCompilation( $rootPluginPath . ConfigFactory::PATH_PARTIAL_DI_CACHE );

		$containerBuilder->addDefinitions( [
			'paths.pluginRoot'           => $rootPluginPath,
			ConfigFactory::class         => function ( ContainerInterface $c ) {
				return new ConfigFactory( $c->get( 'paths.pluginRoot' ) );
			},
			TemplateConfig::class        => function ( ContainerInterface $c ) {
				/** @var ConfigFactory $configFactory */
				$configFactory = $c->get( ConfigFactory::class );

				return $configFactory->createTemplateConfig();
			},
			LoaderInterface::class       => function ( ContainerInterface $c ) {
				/** @var TemplateConfig $templateConfig */
				$templateConfig = $c->get( TemplateConfig::class );

				return new FilesystemLoader( $templateConfig->templateRootPath );
			},
			Environment::class           => function ( ContainerInterface $c ) {

				/** @var TemplateConfig $templateConfig */
				$templateConfig = $c->get( TemplateConfig::class );

				return new Environment( $c->get( LoaderInterface::class ), [
					'cache' => $templateConfig->templateCachePath
				] );
			},
			IWordPressMenuFactory::class => DI\autowire( WordPressMenuFactory::class ),
			Plugin::class                => DI\create( Plugin::class )->constructor( DI\get( WordPressMenuFactory::class ) )
		] );

		return $containerBuilder->build();
	}
}
