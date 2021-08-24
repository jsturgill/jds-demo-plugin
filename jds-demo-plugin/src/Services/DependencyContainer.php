<?php

namespace JdsDemoPlugin\Services;

use DI;
use Exception;
use JdsDemoPlugin\Config\ConfigFactory;
use JdsDemoPlugin\Config\TemplateConfig;
use JdsDemoPlugin\Config\TwigTextExtractionConfig;
use JdsDemoPlugin\Plugin;
use JdsDemoPlugin\WordPressApi\Interfaces\IWordPressMenuFactory;
use JdsDemoPlugin\WordPressApi\WordPressMenuFactory;
use Psr\Container\ContainerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;
use Twig\TwigFunction;

class DependencyContainer {
	/**
	 * @var array<string, DI\Container>
	 */
	private static array $containerMap = [];

	/**
	 * Create a DI container
	 *
	 * Each root plugin path provided will create and cache a new container. Subsequent calls for
	 * the same path return the cached container.
	 * @throws Exception
	 */
	public static function create( ?string $rootPluginPath = null ): DI\Container {
		$rootPluginPath = $rootPluginPath ?? dirname( ( __DIR__ ), 2 );

		// force a trailing slash
		$rootPluginPath = rtrim( $rootPluginPath, FileSystem::PATH_SEPARATORS ) . "/";

		if ( array_key_exists( $rootPluginPath, DependencyContainer::$containerMap ) ) {
			return DependencyContainer::$containerMap[ $rootPluginPath ];
		}

		$containerBuilder = new DI\ContainerBuilder();
		$containerBuilder->enableCompilation( $rootPluginPath . ConfigFactory::PATH_PARTIAL_DI_CACHE );

		$containerBuilder->addDefinitions( [
			'paths.pluginRoot'              => $rootPluginPath,
			ConfigFactory::class            => function ( ContainerInterface $c ) {
				return new ConfigFactory( $c->get( FileSystem::class ), $c->get( 'paths.pluginRoot' ) );
			},
			TemplateConfig::class           => function ( ContainerInterface $c ) {
				/** @var ConfigFactory $configFactory */
				$configFactory = $c->get( ConfigFactory::class );

				return $configFactory->createTemplateConfig();
			},
			TwigTextExtractionConfig::class => function ( ContainerInterface $c ) {
				/** @var ConfigFactory $configFactory */
				$configFactory = $c->get( ConfigFactory::class );

				return $configFactory->createTwigTextExtractionConfig();
			},
			LoaderInterface::class          => function ( ContainerInterface $c ) {
				/** @var TemplateConfig $templateConfig */
				$templateConfig = $c->get( TemplateConfig::class );

				return new FilesystemLoader( $templateConfig->templateRootPath );
			},
			Environment::class              => function ( ContainerInterface $c ) {
				/** @var TemplateConfig $templateConfig */
				$templateConfig = $c->get( TemplateConfig::class );

				$twig = new Environment( $c->get( LoaderInterface::class ), [
					'cache' => $templateConfig->templateCachePath
				] );

				// each translation function has an additional trailing argument
				// for comments that are ignored here
				// -- however, the TwigTextExtractor class recognizes the argument
				// if it is present
				$twig->addFunction( new TwigFunction( '__', function ( string $text, ?string $comment = null ): string {
					return __( $text, 'jds-demo-plugin-domain' );
				} ) );

				$twig->addFunction( new TwigFunction( '_e', function ( string $text, ?string $comment = null ): void {
					if ( ! function_exists( '_e' ) ) {
						// dummy function -- actually equivalent to WP's version
						function _e( $arg ) {
							echo __( $arg );
						}
					}
					_e( $text, 'jds-demo-plugin-domain' );
				} ) );

				$twig->addFunction( new TwigFunction( '_x', function ( string $text, string $context, $comment = null ) {
					if ( ! function_exists( '_x' ) ) {
						// dummy function -- not equivalent to WP's version
						function _x( $arg ): string {
							return __( $arg );
						}
					}
					_x( $text, $context, 'jds-demo-plugin-domain' );
				} ) );

				return $twig;
			},
			TwigTextExtractor::class        => DI\autowire( TwigTextExtractor::class ),
			IWordPressMenuFactory::class    => DI\autowire( WordPressMenuFactory::class ),
			Plugin::class                   => DI\autowire( Plugin::class ),
			FileSystem::class               => DI\create( FileSystem::class )->constructor( $rootPluginPath, true )
		] );

		self::$containerMap[ $rootPluginPath ] = $containerBuilder->build();

		return self::$containerMap[ $rootPluginPath ];
	}
}
