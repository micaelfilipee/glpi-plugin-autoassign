<?php

include '../../../inc/includes.php';
require_once __DIR__ . '/../inc/autoassign.class.php';

Session::checkRight('config', READ);

$rulecollection = new PluginAutoassignRuleShowAllCollection();

include GLPI_ROOT . '/front/rule.common.php';
