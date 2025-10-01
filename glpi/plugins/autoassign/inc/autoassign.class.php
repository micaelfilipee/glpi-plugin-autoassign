<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginAutoassignRuleShowAllCollection extends RuleCollection
{
    public $menu_type   = 'plugins';
    public $menu_option = 'autoassign';

    private function showCreateRuleButton($target)
    {
        global $CFG_GLPI;

        if (!static::canCreate()) {
            return;
        }

        $back = $_SERVER['REQUEST_URI'] ?? $target;

        $params = [
            'sub_type' => $this->getRuleClassName(),
            'back'     => $back,
        ];

        $query = Toolbox::append_params($params, '&');
        $url   = $CFG_GLPI['root_doc'] . '/front/rule.form.php?' . $query;

        echo "<div class='spaced center'>";
        echo "<a class='vsubmit' href='" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "'>" .
            __('Adicionar regra de visibilidade', 'autoassign') .
            '</a>';
        echo '</div>';
    }

    public function getTitle()
    {
        return __('Regras para substituir a visibilidade de entidades', 'autoassign');
    }

    public function canList()
    {
        return Session::haveRight('config', READ);
    }

    public function prepareInputDataForProcess($input, $params)
    {
        $userID = (int) ($params['users_id'] ?? $input['users_id'] ?? 0);

        if ($userID <= 0) {
            return $input;
        }

        $fields = array_map('Toolbox::strtolower', $this->getFieldsToLookFor());

        $input['users_id'] = $userID;

        if (in_array('profiles_id', $fields, true)) {
            $input['profiles_id'] = PluginAutoassignRuleHelper::getUserProfiles($userID);
        }

        if (in_array('groups_id', $fields, true)) {
            $input['groups_id'] = PluginAutoassignRuleHelper::getUserGroups($userID);
        }

        if (in_array('entities_id', $fields, true)) {
            $input['entities_id'] = PluginAutoassignRuleHelper::getUserEntities($userID);
        }

        return $input;
    }

    public function showAdditionalInformationsInForm($target)
    {
        $this->showCreateRuleButton($target);
    }
}

class PluginAutoassignRuleShowAll extends Rule
{
    public $can_sort = true;

    public static function getTypeName($nb = 0)
    {
        return _n('Regra para mostrar todas as entidades', 'Regras para mostrar todas as entidades', $nb, 'autoassign');
    }

    public function getTitle()
    {
        return __('Regras para substituir a visibilidade de entidades', 'autoassign');
    }

    public function getCriterias()
    {
        $criterias = [];

        $criterias['users_id']['field'] = 'name';
        $criterias['users_id']['name']  = __('User');
        $criterias['users_id']['table'] = User::getTable();
        $criterias['users_id']['type']  = 'dropdown_users';

        $criterias['profiles_id']['field']   = 'name';
        $criterias['profiles_id']['name']    = Profile::getTypeName(1);
        $criterias['profiles_id']['table']   = Profile::getTable();
        $criterias['profiles_id']['type']    = 'dropdown';
        $criterias['profiles_id']['virtual'] = true;

        $criterias['groups_id']['field']     = 'completename';
        $criterias['groups_id']['name']      = Group::getTypeName(1);
        $criterias['groups_id']['table']     = Group::getTable();
        $criterias['groups_id']['type']      = 'dropdown';
        $criterias['groups_id']['virtual']   = true;
        $criterias['groups_id']['condition'] = ['is_assign' => 1];

        $criterias['entities_id']['field']           = 'completename';
        $criterias['entities_id']['name']            = Entity::getTypeName(1);
        $criterias['entities_id']['table']           = Entity::getTable();
        $criterias['entities_id']['type']            = 'dropdown';
        $criterias['entities_id']['virtual']         = true;
        $criterias['entities_id']['allow_condition'] = [
            Rule::PATTERN_IS,
            Rule::PATTERN_UNDER,
            Rule::PATTERN_NOT_UNDER,
        ];

        return $criterias;
    }

    public function getActions()
    {
        $actions = [];

        $actions['force_showall']['name']          = __('Forçar exibição de todas as entidades', 'autoassign');
        $actions['force_showall']['type']          = 'yesonly';
        $actions['force_showall']['force_actions'] = ['assign'];

        return $actions;
    }

    public function maxActionsCount()
    {
        return 1;
    }
}

class PluginAutoassignRuleAssignCollection extends RuleCollection
{
    public $menu_type             = 'plugins';
    public $menu_option           = 'autoassign';
    public $stop_on_first_match   = false;

    private function showCreateRuleButton($target)
    {
        global $CFG_GLPI;

        if (!static::canCreate()) {
            return;
        }

        $back = $_SERVER['REQUEST_URI'] ?? $target;

        $params = [
            'sub_type' => $this->getRuleClassName(),
            'back'     => $back,
        ];

        $query = Toolbox::append_params($params, '&');
        $url   = $CFG_GLPI['root_doc'] . '/front/rule.form.php?' . $query;

        echo "<div class='spaced center'>";
        echo "<a class='vsubmit' href='" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "'>" .
            __('Adicionar regra de atribuição', 'autoassign') .
            '</a>';
        echo '</div>';
    }

    public function getTitle()
    {
        return __('Regras para atribuições baseadas em tarefas', 'autoassign');
    }

    public function canList()
    {
        return Session::haveRight('config', READ);
    }

    public function prepareInputDataForProcess($input, $params)
    {
        $ticketFields = [];
        if (isset($params['ticket']) && is_array($params['ticket'])) {
            $ticketFields = $params['ticket'];
        } elseif (isset($input['id'])) {
            $ticketFields = $input;
        }

        if (empty($ticketFields)) {
            return $input;
        }

        $fields = array_map('Toolbox::strtolower', $this->getFieldsToLookFor());

        foreach (['entities_id', 'type', 'itilcategories_id', 'status', 'requesttypes_id', 'global_validation', 'priority'] as $field) {
            if (in_array($field, $fields, true) && array_key_exists($field, $ticketFields)) {
                $input[$field] = $ticketFields[$field];
            }
        }

        if (in_array('tag', $fields, true)) {
            $input['tag'] = PluginAutoassignRuleHelper::getTicketTags((int) ($ticketFields['id'] ?? 0), $ticketFields);
        }

        return $input;
    }

    public function showAdditionalInformationsInForm($target)
    {
        $this->showCreateRuleButton($target);
    }
}

class PluginAutoassignRuleAssign extends Rule
{
    public $can_sort = true;

    public static function getTypeName($nb = 0)
    {
        return _n('Regra de atribuição automática', 'Regras de atribuição automática', $nb, 'autoassign');
    }

    public function getTitle()
    {
        return __('Regras para atribuições baseadas em tarefas', 'autoassign');
    }

    public function maybeRecursive()
    {
        return true;
    }

    public function isEntityAssign()
    {
        return true;
    }

    public function getCriterias()
    {
        $criterias = [];

        $criterias['entities_id']['field']           = 'completename';
        $criterias['entities_id']['name']            = Entity::getTypeName(1);
        $criterias['entities_id']['table']           = Entity::getTable();
        $criterias['entities_id']['type']            = 'dropdown';
        $criterias['entities_id']['allow_condition'] = [
            Rule::PATTERN_IS,
            Rule::PATTERN_UNDER,
            Rule::PATTERN_NOT_UNDER,
        ];

        $criterias['tag']['name'] = __('Etiqueta', 'autoassign');
        $criterias['tag']['type'] = 'text';

        $criterias['type']['name']      = __('Type');
        $criterias['type']['type']      = 'dropdown_tickettype';
        $criterias['type']['linkfield'] = 'type';

        $criterias['itilcategories_id']['field'] = 'completename';
        $criterias['itilcategories_id']['name']  = ITILCategory::getTypeName(1);
        $criterias['itilcategories_id']['table'] = ITILCategory::getTable();
        $criterias['itilcategories_id']['type']  = 'dropdown';

        $criterias['status']['name'] = __('Status');
        $criterias['status']['type'] = 'dropdown_status';

        $criterias['requesttypes_id']['field'] = 'name';
        $criterias['requesttypes_id']['name']  = RequestType::getTypeName(1);
        $criterias['requesttypes_id']['table'] = RequestType::getTable();
        $criterias['requesttypes_id']['type']  = 'dropdown';

        $criterias['global_validation']['name'] = __('Aprovação', 'autoassign');
        $criterias['global_validation']['type'] = 'dropdown_validation';

        $criterias['priority']['name'] = __('Prioridade', 'autoassign');
        $criterias['priority']['type'] = 'dropdown_priority';

        return $criterias;
    }

    public function getActions()
    {
        $actions = [];

        $actions['autoassign_user']['name']               = __('Atribuir usuário automaticamente', 'autoassign');
        $actions['autoassign_user']['type']               = 'dropdown_users';
        $actions['autoassign_user']['table']              = User::getTable();
        $actions['autoassign_user']['force_actions']      = ['append'];
        $actions['autoassign_user']['permitseveral']      = ['append'];
        $actions['autoassign_user']['appendto']           = '_autoassign_users';
        $actions['autoassign_user']['appendtoarray']      = [];
        $actions['autoassign_user']['appendtoarrayfield'] = 'users_id';

        $actions['autoassign_group']['name']               = __('Atribuir grupo automaticamente', 'autoassign');
        $actions['autoassign_group']['type']               = 'dropdown';
        $actions['autoassign_group']['table']              = Group::getTable();
        $actions['autoassign_group']['condition']          = ['is_assign' => 1];
        $actions['autoassign_group']['force_actions']      = ['append'];
        $actions['autoassign_group']['permitseveral']      = ['append'];
        $actions['autoassign_group']['appendto']           = '_autoassign_groups';
        $actions['autoassign_group']['appendtoarray']      = [];
        $actions['autoassign_group']['appendtoarrayfield'] = 'groups_id';

        return $actions;
    }
}

class PluginAutoassignRuleHelper
{
    private static $memberships = [];

    public static function shouldForceShowAll($userID)
    {
        if ($userID <= 0) {
            return false;
        }

        $collection = new PluginAutoassignRuleShowAllCollection();
        $result     = $collection->processAllRules(
            ['users_id' => $userID],
            [],
            ['users_id' => $userID]
        );

        $result = Toolbox::stripslashes_deep($result);

        if (isset($result['force_showall'])) {
            return (int) $result['force_showall'] === 1;
        }

        return false;
    }

    public static function getAutoAssignTargets(Ticket $ticket)
    {
        $collection = new PluginAutoassignRuleAssignCollection();
        $result     = $collection->processAllRules(
            $ticket->fields,
            [],
            ['ticket' => $ticket->fields]
        );

        $result = Toolbox::stripslashes_deep($result);

        $users  = [];
        $groups = [];

        if (!empty($result['_autoassign_users'])) {
            foreach ($result['_autoassign_users'] as $data) {
                if (!is_array($data)) {
                    continue;
                }
                $users[] = (int) ($data['users_id'] ?? 0);
            }
        }

        if (!empty($result['_autoassign_groups'])) {
            foreach ($result['_autoassign_groups'] as $data) {
                if (!is_array($data)) {
                    continue;
                }
                $groups[] = (int) ($data['groups_id'] ?? 0);
            }
        }

        return [
            'users'  => array_values(array_unique(array_filter($users))),
            'groups' => array_values(array_unique(array_filter($groups))),
        ];
    }

    public static function ensureUserAssignment($ticketID, $userID)
    {
        if ($ticketID <= 0 || $userID <= 0) {
            return;
        }

        global $DB;

        $iterator = $DB->request([
            'FROM'  => 'glpi_tickets_users',
            'WHERE' => [
                'tickets_id' => $ticketID,
                'users_id'   => $userID,
                'type'       => CommonITILActor::ASSIGN,
            ],
            'LIMIT' => 1,
        ]);

        foreach ($iterator as $row) {
            return;
        }

        $ticketUser = new Ticket_User();
        $ticketUser->add([
            'tickets_id' => $ticketID,
            'users_id'   => $userID,
            'type'       => CommonITILActor::ASSIGN,
        ]);
    }

    public static function ensureGroupAssignment($ticketID, $groupID)
    {
        if ($ticketID <= 0 || $groupID <= 0) {
            return;
        }

        global $DB;

        $iterator = $DB->request([
            'FROM'  => 'glpi_groups_tickets',
            'WHERE' => [
                'tickets_id' => $ticketID,
                'groups_id'  => $groupID,
                'type'       => CommonITILActor::ASSIGN,
            ],
            'LIMIT' => 1,
        ]);

        foreach ($iterator as $row) {
            return;
        }

        $ticketGroup = new Group_Ticket();
        $ticketGroup->add([
            'tickets_id' => $ticketID,
            'groups_id'  => $groupID,
            'type'       => CommonITILActor::ASSIGN,
        ]);
    }

    public static function getUserProfiles($userID)
    {
        if ($userID <= 0) {
            return [];
        }

        if (!isset(self::$memberships[$userID]['profiles'])) {
            $profiles = [];
            foreach (Profile_User::getUserProfiles($userID) as $profile) {
                if (isset($profile['profiles_id'])) {
                    $profiles[] = (int) $profile['profiles_id'];
                }
            }
            self::$memberships[$userID]['profiles'] = array_values(array_unique($profiles));
        }

        return self::$memberships[$userID]['profiles'];
    }

    public static function getUserGroups($userID)
    {
        if ($userID <= 0) {
            return [];
        }

        if (!isset(self::$memberships[$userID]['groups'])) {
            $groups = [];
            foreach (Group_User::getUserGroups($userID) as $group) {
                if (isset($group['id'])) {
                    $groups[] = (int) $group['id'];
                }
            }
            self::$memberships[$userID]['groups'] = array_values(array_unique($groups));
        }

        return self::$memberships[$userID]['groups'];
    }

    public static function getUserEntities($userID)
    {
        if ($userID <= 0) {
            return [];
        }

        if (!isset(self::$memberships[$userID]['entities'])) {
            $entities = Profile_User::getUserEntities($userID, true, true);
            self::$memberships[$userID]['entities'] = array_values(array_unique(array_map('intval', $entities)));
        }

        return self::$memberships[$userID]['entities'];
    }

    public static function getTicketTags($ticketID, array $ticketFields = [])
    {
        $tags = [];

        if ($ticketID > 0 && class_exists('Tag')) {
            $tag = new Tag();

            if (method_exists($tag, 'getListForItem')) {
                foreach ((array) $tag->getListForItem('Ticket', $ticketID) as $tagRow) {
                    if (isset($tagRow['tag'])) {
                        $tags[] = $tagRow['tag'];
                    } elseif (isset($tagRow['name'])) {
                        $tags[] = $tagRow['name'];
                    }
                }
            } elseif (method_exists($tag, 'getForItem')) {
                foreach ((array) $tag->getForItem('Ticket', $ticketID) as $tagRow) {
                    if (isset($tagRow['tag'])) {
                        $tags[] = $tagRow['tag'];
                    } elseif (isset($tagRow['name'])) {
                        $tags[] = $tagRow['name'];
                    }
                }
            }
        }

        if (empty($tags) && isset($ticketFields['tag'])) {
            $tags[] = $ticketFields['tag'];
        }

        return array_values(array_filter(array_unique($tags)));
    }
}
