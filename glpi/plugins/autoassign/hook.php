<?php

if (!defined('GLPI_ROOT')) {
    die('Sorry. You can\'t access directly to this file');
}

function plugin_autoassign_force_showall($params = [])
{
    $userID = Session::getLoginUserID();

    if (empty($userID)) {
        return;
    }

    if (plugin_autoassign_user_matches_rule((int) $userID, 'force_showall')) {
        $_SESSION['glpishowallentities'] = 1;
    }
}

function plugin_autoassign_post_item_add(CommonDBTM $item)
{
    if (!($item instanceof TicketTask)) {
        return;
    }

    $userID   = (int) ($item->fields['users_id_tech'] ?? 0);
    $ticketID = (int) ($item->fields['tickets_id'] ?? 0);

    if ($userID <= 0 || $ticketID <= 0) {
        return;
    }

    if (!plugin_autoassign_user_matches_rule($userID, 'autoassign_task')) {
        return;
    }

    global $DB;

    $existing = $DB->request([
        'FROM'  => 'glpi_tickets_users',
        'WHERE' => [
            'tickets_id' => $ticketID,
            'users_id'   => $userID,
            'type'       => CommonITILActor::ASSIGN,
        ],
        'LIMIT' => 1,
    ]);

    if ($existing) {
        foreach ($existing as $row) {
            return;
        }
    }

    $ticketUser = new Ticket_User();
    $ticketUser->add([
        'tickets_id' => $ticketID,
        'users_id'   => $userID,
        'type'       => CommonITILActor::ASSIGN,
    ]);
}

function plugin_autoassign_user_matches_rule($userID, $flagField)
{
    global $DB;

    if ($userID <= 0) {
        return false;
    }

    $memberships = plugin_autoassign_get_user_memberships($userID);

    if (empty($memberships)) {
        return false;
    }

    $iterator = $DB->request([
        'FROM'  => 'glpi_plugin_autoassign_configs',
        'WHERE' => [
            $flagField => 1,
        ],
    ]);

    foreach ($iterator as $rule) {
        $profileID = isset($rule['profiles_id']) ? (int) $rule['profiles_id'] : null;
        $groupID   = isset($rule['groups_id']) ? (int) $rule['groups_id'] : null;
        $entityID  = isset($rule['entities_id']) ? (int) $rule['entities_id'] : null;

        if ($profileID && in_array($profileID, $memberships['profiles'], true)) {
            return true;
        }

        if ($groupID && in_array($groupID, $memberships['groups'], true)) {
            return true;
        }

        if ($entityID !== null && in_array($entityID, $memberships['entities'], true)) {
            return true;
        }
    }

    return false;
}

function plugin_autoassign_get_user_memberships($userID)
{
    global $DB;

    static $cache = [];

    if (isset($cache[$userID])) {
        return $cache[$userID];
    }

    $profiles = [];
    $groups   = [];
    $entities = [];

    $profileIterator = $DB->request([
        'SELECT' => ['profiles_id', 'entities_id'],
        'FROM'   => 'glpi_profiles_users',
        'WHERE'  => ['users_id' => $userID],
    ]);

    foreach ($profileIterator as $row) {
        if ($row['profiles_id'] !== null) {
            $profiles[] = (int) $row['profiles_id'];
        }
        if ($row['entities_id'] !== null) {
            $entities[] = (int) $row['entities_id'];
        }
    }

    $groupIterator = $DB->request([
        'SELECT' => 'groups_id',
        'FROM'   => 'glpi_groups_users',
        'WHERE'  => ['users_id' => $userID],
    ]);

    foreach ($groupIterator as $row) {
        if ($row['groups_id'] !== null) {
            $groups[] = (int) $row['groups_id'];
        }
    }

    $user = new User();
    if ($user->getFromDB($userID)) {
        if (isset($user->fields['entities_id']) && $user->fields['entities_id'] !== null) {
            $entities[] = (int) $user->fields['entities_id'];
        }
    }

    $cache[$userID] = [
        'profiles' => array_values(array_unique($profiles)),
        'groups'   => array_values(array_unique($groups)),
        'entities' => array_values(array_unique($entities)),
    ];

    return $cache[$userID];
}
