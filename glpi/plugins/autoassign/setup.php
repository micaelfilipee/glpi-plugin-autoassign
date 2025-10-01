<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

require_once __DIR__ . '/inc/autoload.php';

define('PLUGIN_AUTOASSIGN_VERSION', '2.0.0');

define('PLUGIN_AUTOASSIGN_LEGACY_TABLE', 'glpi_plugin_autoassign_configs');

function plugin_version_autoassign()
{
    return [
        'name'           => __('Atribuição Automática & Mostrar Tudo', 'autoassign'),
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
            __('Este plugin requer GLPI 9.5.5 ou superior.', 'autoassign'),
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
        echo __('Instalado / não configurado', 'autoassign');
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
}

function plugin_autoassign_install()
{
    global $DB;

    if ($DB->tableExists(PLUGIN_AUTOASSIGN_LEGACY_TABLE)) {
        $DB->query("DROP TABLE `" . PLUGIN_AUTOASSIGN_LEGACY_TABLE . "`");
    }

    return true;
}

function plugin_autoassign_uninstall()
{
    return true;
}

function plugin_autoassign_getConfigPage()
{
    if (Session::haveRight('config', READ)) {
        return 'front/config.form.php';
    }

    return false;
}
