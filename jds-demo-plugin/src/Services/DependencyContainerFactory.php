<?php

namespace JdsDemoPlugin\Services;

use DI;
use Exception;
use JdsDemoPlugin\Config\ConfigFactory;
use JdsDemoPlugin\Config\TemplateConfig;
use JdsDemoPlugin\Config\TwigTextExtractorConfig;
use JdsDemoPlugin\Plugin;
use JdsDemoPlugin\Services\Persistence\IMigrationManagerFactory;
use JdsDemoPlugin\Services\Persistence\MigrationManagerFactory;
use JdsDemoPlugin\WordPressApi\IMenuFactory;
use JdsDemoPlugin\WordPressApi\IPluginLifecycleActionFactory;
use JdsDemoPlugin\WordPressApi\MenuFactory;
use JdsDemoPlugin\WordPressApi\PluginBaseName;
use JdsDemoPlugin\WordPressApi\PluginLifecycleActionFactory;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;
use Twig\TwigFunction;

class DependencyContainerFactory
{
    public const ENV_PROD = 'production';
    public const ENV_TEST = 'test';

    public const LOG_PATH_PARTIAL = 'logs';

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
        $rootPluginPath = rtrim($rootPluginPath, FileSystem::PATH_SEPARATORS) . DIRECTORY_SEPARATOR;

        $containerBuilder = new DI\ContainerBuilder();

        // enable compilation (but only in production)
        if (self::ENV_PROD === $env) {
            $containerBuilder->enableCompilation($rootPluginPath . ConfigFactory::PATH_PARTIAL_DI_CACHE);
        }

        /** @noinspection DuplicatedCode */
        $containerBuilder->addDefinitions([
            'paths.pluginRoot' => $rootPluginPath,
            'paths.loggingFolder' => $rootPluginPath . self::LOG_PATH_PARTIAL . DIRECTORY_SEPARATOR,
            'paths.phinxConfig' => $rootPluginPath . 'phinx.php',
            'paths.pluginFile' => $rootPluginPath . Plugin::PLUGIN_FILE_NAME,
            'keys.translationDomain' => Plugin::TRANSLATION_DOMAIN,
            'keys.environment' => $env,
            'keys.productionEnvironment' => self::ENV_PROD,
            'keys.testEnvironment' => self::ENV_TEST,
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
            LoggerInterface::class => function (ContainerInterface $c) {
                $env = (string)$c->get('keys.environment');
                $testEnvValue = (string)$c->get('keys.testEnvironment');
                $logger = new Logger('jds-demo-plugin::' . $env);
                $level = $env === $testEnvValue
                    ? Logger::INFO
                    : Logger::NOTICE;
                $logFolder = (string)$c->get('paths.loggingFolder');
                $logger->pushHandler(
                    new StreamHandler($logFolder . date('Y-m-d') . '.log', $level)
                );
                return $logger;
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

                $twig->addFunction(new TwigFunction('__', function (
                    string  $text,
                    ?string $comment = null
                ): string {
                    return __($text, 'jds-demo-plugin-domain');
                }));

                $twig->addFunction(new TwigFunction('_e', function (
                    string  $text,
                    ?string $comment = null
                ): void {
                    /** @noinspection PhpUndefinedFunctionInspection */
                    _e($text, 'jds-demo-plugin-domain');
                }));

                $twig->addFunction(new TwigFunction('_x', function (
                    string  $text,
                    string  $context,
                    ?string $comment = null
                ) {
                    /** @noinspection PhpUndefinedFunctionInspection */
                    _x($text, $context, 'jds-demo-plugin-domain');
                }));

                $twig->addFunction(new TwigFunction('_ex', function (
                    string  $text,
                    string  $context,
                    ?string $comment = null
                ): void {
                    /** @noinspection PhpUndefinedFunctionInspection */
                    _ex($text, $context, 'jds-demo-plugin-domain');
                }));

                $twig->addFunction(new TwigFunction('_n', function (
                    string  $single,
                    string  $plural,
                    int     $number,
                    ?string $comment = null
                ) {
                    /** @noinspection PhpUndefinedFunctionInspection */
                    _n($single, $plural, $number, 'jds-demo-plugin-domain');
                }));

                $twig->addFunction(new TwigFunction('_n_noop', function (
                    string  $single,
                    string  $plural,
                    ?string $comment = null
                ) {
                    /** @noinspection PhpUndefinedFunctionInspection */
                    _n_noop($single, $plural, 'jds-demo-plugin-domain');
                }));

                $twig->addFunction(new TwigFunction('_nx', function (
                    string  $single,
                    string  $plural,
                    int     $number,
                    string  $context,
                    ?string $comment = null
                ) {
                    /** @noinspection PhpUndefinedFunctionInspection */
                    _nx($single, $plural, $number, $context, 'jds-demo-plugin-domain');
                }));

                $twig->addFunction(new TwigFunction('_nx_noop', function (
                    string  $single,
                    string  $plural,
                    string  $context,
                    ?string $comment = null
                ) {
                    /** @noinspection PhpUndefinedFunctionInspection */
                    _nx_noop($single, $plural, $context, 'jds-demo-plugin-domain');
                }));

                // the following translation functions are not used by the TwigTextExtractor
                $twig->addFunction(new TwigFunction('translate', function (string $text): string {
                    /** @noinspection PhpUndefinedFunctionInspection */
                    return translate($text, 'jds-demo-plugin-domain');
                }));

                $twig->addFunction(new TwigFunction('translate_nooped_plural', function (
                    array $noopedPlural,
                    int   $count
                ) {
                    /** @noinspection PhpUndefinedFunctionInspection */
                    translate_nooped_plural($noopedPlural, $count, 'jds-demo-plugin-domain');
                }));

                return $twig;
            },
            TwigTextExtractor::class => DI\autowire(TwigTextExtractor::class),
            IMenuFactory::class => DI\autowire(MenuFactory::class),
            PluginBaseName::class => DI\create(PluginBaseName::class)->constructor(DI\get('paths.pluginFile')),
            Plugin::class => DI\autowire(Plugin::class),
            FileSystem::class => DI\create(FileSystem::class)->constructor($rootPluginPath, true),
            IMigrationManagerFactory::class => DI\autowire(MigrationManagerFactory::class)
                ->constructorParameter('configPath', DI\get('paths.phinxConfig')),
            IPluginLifecycleActionFactory::class => DI\autowire(PluginLifecycleActionFactory::class)
        ]);

        return $containerBuilder->build();
    }
}
