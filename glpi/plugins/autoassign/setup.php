<?php

if (!defined('GLPI_ROOT')) {
    die('Sorry. You can\'t access directly to this file');
}

define('PLUGIN_AUTOASSIGN_VERSION', '1.0.0');
define('PLUGIN_AUTOASSIGN_TABLE', 'glpi_plugin_autoassign_configs');

function plugin_version_autoassign()
{
    return [
        'name'           => __('Auto Assign & ShowAll', 'autoassign'),
        'version'        => PLUGIN_AUTOASSIGN_VERSION,
        'author'         => 'Autoassign Plugin Generator',
        'license'        => 'GPLv2+',
        'homepage'       => 'https://example.com',
        'minGlpiVersion' => '9.5.5',
    ];
}

function plugin_autoassign_check_prerequisites()
{
    if (version_compare(GLPI_VERSION, '9.5.5', '<')) {
        Session::addMessageAfterRedirect(
            __('This plugin requires GLPI 9.5.5 or higher.', 'autoassign'),
            false,
            ERROR
        );
        return false;
    }

    return true;
}

function plugin_autoassign_check_config($verbose = false)
{
    if ($verbose) {
        echo __('Installed / not configured', 'autoassign');
    }
    return true;
}

function plugin_init_autoassign()
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['autoassign'] = true;
    $PLUGIN_HOOKS['config_page']['autoassign']    = 'front/config.form.php';
    $PLUGIN_HOOKS['login']['autoassign']          = 'plugin_autoassign_force_showall';
    $PLUGIN_HOOKS['post_item_add']['autoassign']  = 'plugin_autoassign_post_item_add';

    Plugin::registerClass('PluginAutoassignConfig');
}

function plugin_autoassign_install()
{
    global $DB;

    $table = PLUGIN_AUTOASSIGN_TABLE;

    if (!$DB->tableExists($table)) {
        $query = "CREATE TABLE `{$table}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `profiles_id` int(11) DEFAULT NULL,
            `groups_id` int(11) DEFAULT NULL,
            `entities_id` int(11) DEFAULT NULL,
            `force_showall` tinyint(1) NOT NULL DEFAULT '0',
            `autoassign_task` tinyint(1) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $DB->query($query);
    }

    return true;
}

function plugin_autoassign_uninstall()
{
    global $DB;

    $table = PLUGIN_AUTOASSIGN_TABLE;

    if ($DB->tableExists($table)) {
        $DB->query("DROP TABLE `{$table}`");
    }

    return true;
}

function plugin_autoassign_getConfigPage()
{
    if (Session::haveRight('config', READ)) {
        return 'front/config.form.php';
    }

    return false;
}
