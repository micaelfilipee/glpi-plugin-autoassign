<?php

include '../../../inc/includes.php';
require_once __DIR__ . '/../inc/autoload.php';

global $CFG_GLPI;

Session::checkRight('config', READ);

$plugin = new Plugin();
if (!$plugin->isInstalled('autoassign') || !$plugin->isActivated('autoassign')) {
    Html::displayNotFoundError();
}

Html::header(__('Atribuição Automática & Mostrar Tudo', 'autoassign'), $_SERVER['PHP_SELF'], 'config', 'plugins', 'autoassign');

echo "<div class='spaced'>";

$showallUrl = $CFG_GLPI['root_doc'] . '/plugins/autoassign/front/rule_showall.php';
$assignUrl  = $CFG_GLPI['root_doc'] . '/plugins/autoassign/front/rule_assign.php';

echo "<table class='tab_cadre_fixe'>";

echo "<tr><th colspan='2'>" . __('Gerenciar regras', 'autoassign') . '</th></tr>';

echo "<tr class='tab_bg_1'>";

echo "<td><strong>" . __('Regras de visibilidade de entidades', 'autoassign') . "</strong><br>";

echo __('Configurar quais usuários, grupos, perfis ou entidades devem visualizar automaticamente todas as entidades ao fazer login.', 'autoassign');

echo '</td>';

echo "<td class='center'>";

echo "<a class='vsubmit' href='{$showallUrl}'>" . __('Abrir regras', 'autoassign') . '</a>';

echo '</td>';

echo '</tr>';

echo "<tr class='tab_bg_1'>";

echo "<td><strong>" . __('Atribuições de chamados baseadas em tarefas', 'autoassign') . "</strong><br>";

echo __('Defina critérios para atribuir automaticamente técnicos ou grupos aos chamados quando forem adicionados às tarefas.', 'autoassign');

echo '</td>';

echo "<td class='center'>";

echo "<a class='vsubmit' href='{$assignUrl}'>" . __('Abrir regras', 'autoassign') . '</a>';

echo '</td>';

echo '</tr>';

echo '</table>';

echo '</div>';

Html::footer();
