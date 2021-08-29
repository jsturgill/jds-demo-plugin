<?php

namespace JdsDemoPlugin;

use JdsDemoPlugin\Exceptions\CommandFailureException;
use JdsDemoPlugin\Services\Persistence\IMigrationManager;
use JdsDemoPlugin\Services\Persistence\IMigrationManagerFactory;
use JdsDemoPlugin\WordPressApi\IMenuFactory;
use JdsDemoPlugin\WordPressApi\IPluginLifecycleActionFactory;
use JdsDemoPlugin\WordPressApi\Menu;
use JdsDemoPlugin\WordPressApi\PluginBaseName;
use JdsDemoPlugin\WordPressApi\PluginLifecycleAction;
use Psr\Log\LoggerInterface;

class Plugin
{
    public const NAME_BANK = ['You', 'Person', 'World', 'Dolly'];
    public const TRANSLATION_DOMAIN = 'jds-demo-plugin-domain';
    public const TEMPLATE_OPTIONS_MENU = 'jds-demo-plugin-options.twig';
    public const PLUGIN_FILE_NAME = 'jds-demo-plugin.php';

    /**
     * @var array<PluginLifecycleAction>
     */
    private array $lifecycleActions = [];
    private IMigrationManagerFactory $migrationManagerFactory;
    private Menu $optionsMenu;
    private PluginBaseName $pluginBaseName;
    private LoggerInterface $logger;
    private IMigrationManager $migrationManager;

    public function __construct(
        PluginBaseName                $pluginBaseName,
        IMenuFactory                  $menuFactory,
        IPluginLifecycleActionFactory $pluginLifecycleActionFactory,
        IMigrationManagerFactory      $migrationManagerFactory,
        LoggerInterface               $logger
    ) {
        $this->logger = $logger;
        $this->pluginBaseName = $pluginBaseName;
        $this->migrationManagerFactory = $migrationManagerFactory;

        // migrate on activation
        array_push(
            $this->lifecycleActions,
            $pluginLifecycleActionFactory->createAction(
                (string)$this->pluginBaseName,
                PluginLifecycleAction::STAGE_ACTIVATION,
                function () {
                    $this->logger->notice('plugin activated');
                    $result = $this->migrateAndSeed();
                    if (false === $result) {
                        throw new CommandFailureException("Unable to stage required database changes. Check log for details.");
                    }
                }
            )
        );

        // log deactivation
        array_push(
            $this->lifecycleActions,
            $pluginLifecycleActionFactory->createAction(
                (string)$this->pluginBaseName,
                PluginLifecycleAction::STAGE_DEACTIVATION,
                fn () => $this->logger->notice("plugin deactivated")
            )
        );

        array_push(
            $this->lifecycleActions,
            $pluginLifecycleActionFactory->createAction(
                (string)$this->pluginBaseName,
                PluginLifecycleAction::STAGE_UNINSTALL,
                function () {
                    $this->logger->notice("plugin uninstalled");
                    die;
                }
            )
        );

        $this->optionsMenu = $menuFactory->createMenuWithTemplate(
            "options-general.php",
            __("JDS Demo Plugin", "jds-demo-plugin-domain"),
            __("JDS Demo Plugin", "jds-demo-plugin-domain"),
            "manage_options",
            "jds-demo-plugin-options",
            Plugin::TEMPLATE_OPTIONS_MENU,
            fn () => ['audience' => self::NAME_BANK[array_rand(self::NAME_BANK)]]
        );
    }

    /**
     * @return bool true if both migration and seeding go off without a hitch
     */
    public function migrateAndSeed(): bool
    {
        // TODO display some sort of warning / message if this fails -- take additional action as well?
        $this->migrationManager ??= $this->migrationManagerFactory->create();
        $result = $this->migrationManager->migrate();
        $this->logger->notice("migration attempted", ['result (true === success)' => $result]);
        if (true !== $result) {
            return false;
        }
        $result = $this->migrationManager->seed();
        $this->logger->notice('database seed operation attempted', ['result (true === success)' => $result]);
        return $result;
    }

    public function getOptionsMenu(): Menu
    {
        return $this->optionsMenu;
    }
}
