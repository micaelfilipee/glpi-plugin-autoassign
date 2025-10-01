<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

if (!defined('PLUGIN_AUTOASSIGN_AUTOLOADER_REGISTERED')) {
    spl_autoload_register(function ($class) {
        if (strpos($class, 'PluginAutoassign') !== 0) {
            return;
        }

        $filepath = __DIR__ . '/autoassign.class.php';
        if (is_readable($filepath)) {
            require_once $filepath;
        }
    });

    define('PLUGIN_AUTOASSIGN_AUTOLOADER_REGISTERED', true);
}
