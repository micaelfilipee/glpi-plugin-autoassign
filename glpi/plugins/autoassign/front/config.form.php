<?php

include '../../../inc/includes.php';
require_once __DIR__ . '/../inc/autoassign.class.php';

global $CFG_GLPI;

Session::checkRight('config', READ);

$plugin = new Plugin();
if (!$plugin->isInstalled('autoassign') || !$plugin->isActivated('autoassign')) {
    Html::displayNotFoundError();
}

Html::header(__('Auto Assign & ShowAll', 'autoassign'), $_SERVER['PHP_SELF'], 'config', 'plugins', 'autoassign');

echo "<div class='spaced'>";

$showallUrl = $CFG_GLPI['root_doc'] . '/plugins/autoassign/front/rule_showall.php';
$assignUrl  = $CFG_GLPI['root_doc'] . '/plugins/autoassign/front/rule_assign.php';

echo "<table class='tab_cadre_fixe'>";

echo "<tr><th colspan='2'>" . __('Manage rules', 'autoassign') . '</th></tr>';

echo "<tr class='tab_bg_1'>";

echo "<td><strong>" . __('Entity visibility rules', 'autoassign') . "</strong><br>";

echo __('Configure which users, groups, profiles or entities should automatically see every entity on login.', 'autoassign');

echo '</td>';

echo "<td class='center'>";

echo "<a class='vsubmit' href='{$showallUrl}'>" . __('Open rules', 'autoassign') . '</a>';

echo '</td>';

echo '</tr>';

echo "<tr class='tab_bg_1'>";

echo "<td><strong>" . __('Task based ticket assignments', 'autoassign') . "</strong><br>";

echo __('Define criteria to automatically assign technicians or groups to tickets when they are added to tasks.', 'autoassign');

echo '</td>';

echo "<td class='center'>";

echo "<a class='vsubmit' href='{$assignUrl}'>" . __('Open rules', 'autoassign') . '</a>';

echo '</td>';

echo '</tr>';

echo '</table>';

echo '</div>';

Html::footer();
