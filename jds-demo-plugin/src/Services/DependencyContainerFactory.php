<?php

namespace JdsDemoPlugin\Services;

use DI;
use Exception;
use JdsDemoPlugin\Config\ConfigFactory;
use JdsDemoPlugin\Config\TemplateConfig;
use JdsDemoPlugin\Config\TwigTextExtractorConfig;
use JdsDemoPlugin\Plugin;
use JdsDemoPlugin\WordPressApi\Interfaces\IWordPressMenuFactory;
use JdsDemoPlugin\WordPressApi\WordPressMenuFactory;
use Psr\Container\ContainerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;
use Twig\TwigFunction;

class DependencyContainerFactory
{
	const ENV_PROD = 'production';
	const ENV_TEST = 'test';

	/**
	 * @psalm-suppress UnusedClosureParam
	 * Create a DI container
	 * @throws Exception
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function create(?string $rootPluginPath = null, string $env = self::ENV_PROD): DI\Container
	{
		$rootPluginPath = $rootPluginPath ?? dirname((__DIR__), 2);

		// force a trailing slash
		$rootPluginPath = rtrim($rootPluginPath, FileSystem::PATH_SEPARATORS) . "/";

		$containerBuilder = new DI\ContainerBuilder();

		// enable compilation (but only in production)
		if (self::ENV_PROD === $env) {
			$containerBuilder->enableCompilation($rootPluginPath . ConfigFactory::PATH_PARTIAL_DI_CACHE);
		}

		$containerBuilder->addDefinitions([
			'paths.pluginRoot' => $rootPluginPath,
			'keys.translationDomain' => Plugin::TRANSLATION_DOMAIN,
			ConfigFactory::class => function (ContainerInterface $c) {
				/** @var FileSystem $fileSystem */
				$fileSystem = $c->get(FileSystem::class);
				return new ConfigFactory($fileSystem, (string)$c->get('paths.pluginRoot'), (string)$c->get('keys.translationDomain'));
			},
			TemplateConfig::class => function (ContainerInterface $c) {
				/** @var ConfigFactory $configFactory */
				$configFactory = $c->get(ConfigFactory::class);

				return $configFactory->createTemplateConfig();
			},
			TwigTextExtractorConfig::class => function (ContainerInterface $c) {
				/** @var ConfigFactory $configFactory */
				$configFactory = $c->get(ConfigFactory::class);

				return $configFactory->createTwigTextExtractionConfig();
			},
			LoaderInterface::class => function (ContainerInterface $c) {
				/** @var TemplateConfig $templateConfig */
				$templateConfig = $c->get(TemplateConfig::class);

				return new FilesystemLoader($templateConfig->templateRootPath);
			},
			Environment::class => function (ContainerInterface $c) {
				/** @var TemplateConfig $templateConfig */
				$templateConfig = $c->get(TemplateConfig::class);

				/** @var LoaderInterface $loader */
				$loader = $c->get(LoaderInterface::class);
				$twig = new Environment($loader, [
					'cache' => $templateConfig->templateCachePath
				]);

				// each translation function has an additional trailing argument
				// for comments that are ignored here
				// -- however, the TwigTextExtractor class recognizes the argument
				// if it is present

				$twig->addFunction(new TwigFunction('__', function (string  $text,
																	?string $comment = null): string {
					return __($text, 'jds-demo-plugin-domain');
				}));

				$twig->addFunction(new TwigFunction('_e', function (string  $text,
																	?string $comment = null): void {
					_e($text, 'jds-demo-plugin-domain');
				}));

				$twig->addFunction(new TwigFunction('_x', function (string  $text,
																	string  $context,
																	?string $comment = null) {
					_x($text, $context, 'jds-demo-plugin-domain');
				}));

				$twig->addFunction(new TwigFunction('_ex', function (string  $text,
																	 string  $context,
																	 ?string $comment = null): void {
					_ex($text, $context, 'jds-demo-plugin-domain');
				}));

				$twig->addFunction(new TwigFunction('_n', function (string  $single,
																	string  $plural,
																	int     $number,
																	?string $comment = null) {
					_n($single, $plural, $number, 'jds-demo-plugin-domain');
				}));

				$twig->addFunction(new TwigFunction('_n_noop', function (string  $single,
																		 string  $plural,
																		 ?string $comment = null) {
					_n_noop($single, $plural, 'jds-demo-plugin-domain');
				}));

				$twig->addFunction(new TwigFunction('_nx', function (string  $single,
																	 string  $plural,
																	 int     $number,
																	 string  $context,
																	 ?string $comment = null) {
					_nx($single, $plural, $number, $context, 'jds-demo-plugin-domain');
				}));

				$twig->addFunction(new TwigFunction('_nx_noop', function (string  $single,
																		  string  $plural,
																		  string  $context,
																		  ?string $comment = null) {
					_nx_noop($single, $plural, $context, 'jds-demo-plugin-domain');
				}));

				// the following translation functions are not used by the TwigTextExtractor
				$twig->addFunction(new TwigFunction('translate', function (string $text): string {
					return translate($text, 'jds-demo-plugin-domain');
				}));

				$twig->addFunction(new TwigFunction('translate_nooped_plural', function (array $noopedPlural,
																						 int   $count) {
					translate_nooped_plural($noopedPlural, $count, 'jds-demo-plugin-domain');
				}));


				return $twig;
			},
			TwigTextExtractor::class => DI\autowire(TwigTextExtractor::class),
			IWordPressMenuFactory::class => DI\autowire(WordPressMenuFactory::class),
			Plugin::class => DI\autowire(Plugin::class),
			FileSystem::class => DI\create(FileSystem::class)->constructor($rootPluginPath, true)
		]);

		return $containerBuilder->build();
	}
}
