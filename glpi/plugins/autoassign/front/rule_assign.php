<?php

include '../../../inc/includes.php';
require_once __DIR__ . '/../inc/autoload.php';

Session::checkRight('config', READ);

$rulecollection = new PluginAutoassignRuleAssignCollection();

include GLPI_ROOT . '/front/rule.common.php';
