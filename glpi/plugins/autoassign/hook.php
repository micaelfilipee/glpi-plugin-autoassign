<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

require_once __DIR__ . '/inc/autoassign.class.php';

function plugin_autoassign_force_showall($params = [])
{
    $userID = (int) Session::getLoginUserID();

    if ($userID <= 0) {
        return;
    }

    if (PluginAutoassignRuleHelper::shouldForceShowAll($userID)) {
        $_SESSION['glpishowallentities'] = 1;
    }
}

function plugin_autoassign_post_item_add(CommonDBTM $item)
{
    if (!($item instanceof TicketTask)) {
        return;
    }

    $userID   = (int) ($item->fields['users_id_tech'] ?? 0);
    $groupID  = (int) ($item->fields['groups_id_tech'] ?? 0);
    $ticketID = (int) ($item->fields['tickets_id'] ?? 0);

    if (($userID <= 0 && $groupID <= 0) || $ticketID <= 0) {
        return;
    }

    $ticket = new Ticket();
    if (!$ticket->getFromDB($ticketID)) {
        return;
    }

    $targets = PluginAutoassignRuleHelper::getAutoAssignTargets($ticket);

    if ($userID > 0 && in_array($userID, $targets['users'], true)) {
        PluginAutoassignRuleHelper::ensureUserAssignment($ticketID, $userID);
    }

    if ($groupID > 0 && in_array($groupID, $targets['groups'], true)) {
        PluginAutoassignRuleHelper::ensureGroupAssignment($ticketID, $groupID);
    }
}
