<?php

include '../../../inc/includes.php';
require_once __DIR__ . '/../inc/autoassign.class.php';

global $CFG_GLPI;

Session::checkRight('config', READ);

$plugin = new Plugin();
if (!$plugin->isInstalled('autoassign') || !$plugin->isActivated('autoassign')) {
    Html::displayNotFoundError();
}

$config = new PluginAutoassignConfig();

if (isset($_POST['add'])) {
    $config->check(-1, CREATE, $_POST);
    $config->add($_POST);
    Html::redirect($CFG_GLPI['root_doc'] . '/plugins/autoassign/front/config.form.php');
}

if (isset($_POST['update'])) {
    $config->check($_POST['id'], UPDATE);
    $config->update($_POST);
    Html::redirect($CFG_GLPI['root_doc'] . '/plugins/autoassign/front/config.form.php');
}

if (isset($_POST['delete'])) {
    $config->check($_POST['id'], DELETE);
    $config->delete($_POST);
    Html::redirect($CFG_GLPI['root_doc'] . '/plugins/autoassign/front/config.form.php');
}

if (isset($_POST['purge'])) {
    $config->check($_POST['id'], PURGE);
    $config->delete($_POST, true);
    Html::redirect($CFG_GLPI['root_doc'] . '/plugins/autoassign/front/config.form.php');
}

$ID = isset($_GET['id']) ? (int) $_GET['id'] : 0;

Html::header(__('Auto Assign & ShowAll', 'autoassign'), $_SERVER['PHP_SELF'], 'config', 'plugins', 'autoassign');

echo "<div class='spaced'>";
$config->showForm($ID);
echo '</div>';

echo "<div class='spaced'>";
Search::show('PluginAutoassignConfig');
echo '</div>';

Html::footer();
