<?php

if (!defined('GLPI_ROOT')) {
    die('Sorry. You can\'t access directly to this file');
}

class PluginAutoassignConfig extends CommonDBTM
{
    public static $rightname = 'config';

    public static $table = 'glpi_plugin_autoassign_configs';

    public static function getTypeName($nb = 0)
    {
        return _n('Auto assign rule', 'Auto assign rules', $nb, 'autoassign');
    }

    public function prepareInputForAdd($input)
    {
        $input = parent::prepareInputForAdd($input);
        if ($input === false) {
            return false;
        }

        return $this->sanitizeInput($input);
    }

    public function prepareInputForUpdate($input)
    {
        $input = parent::prepareInputForUpdate($input);
        if ($input === false) {
            return false;
        }

        return $this->sanitizeInput($input);
    }

    private function sanitizeInput(array $input)
    {
        foreach (['profiles_id', 'groups_id', 'entities_id'] as $field) {
            if (!array_key_exists($field, $input)) {
                continue;
            }

            if ($input[$field] === '' || $input[$field] === null) {
                $input[$field] = null;
                continue;
            }

            $value = (int) $input[$field];

            if ($field === 'entities_id') {
                $input[$field] = $value;
            } else {
                $input[$field] = $value > 0 ? $value : null;
            }
        }

        $input['force_showall']   = isset($input['force_showall']) ? 1 : 0;
        $input['autoassign_task'] = isset($input['autoassign_task']) ? 1 : 0;

        return $input;
    }

    public static function canView()
    {
        return Session::haveRight(self::$rightname, READ);
    }

    public static function canCreate()
    {
        return Session::haveRight(self::$rightname, UPDATE);
    }

    public static function canUpdate()
    {
        return Session::haveRight(self::$rightname, UPDATE);
    }

    public static function canDelete()
    {
        return Session::haveRight(self::$rightname, UPDATE);
    }

    public static function canPurge()
    {
        return Session::haveRight(self::$rightname, UPDATE);
    }

    public function showForm($ID, array $options = [])
    {
        if ($ID > 0) {
            $this->check($ID, READ);
        } else {
            $this->getEmpty();

            if (!self::canCreate()) {
                Html::displayRightError();
            }
        }

        $this->initForm($ID, $options);
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";
        echo '<td>' . __('Profile') . '</td>';
        echo '<td>';
        Profile::dropdown([
            'name'                  => 'profiles_id',
            'value'                 => $this->fields['profiles_id'] ?? 0,
            'display_emptychoice'   => true,
            'checkright'            => false,
        ]);
        echo '</td>';
        echo '<td>' . __('Force show all entities', 'autoassign') . '</td>';
        echo '<td>';
        Html::showCheckbox([
            'name'    => 'force_showall',
            'checked' => !empty($this->fields['force_showall']),
        ]);
        echo '</td>';
        echo '</tr>';

        echo "<tr class='tab_bg_1'>";
        echo '<td>' . __('Group') . '</td>';
        echo '<td>';
        Group::dropdown([
            'name'                => 'groups_id',
            'value'               => $this->fields['groups_id'] ?? 0,
            'display_emptychoice' => true,
        ]);
        echo '</td>';
        echo '<td>' . __('Auto assign task technician', 'autoassign') . '</td>';
        echo '<td>';
        Html::showCheckbox([
            'name'    => 'autoassign_task',
            'checked' => !empty($this->fields['autoassign_task']),
        ]);
        echo '</td>';
        echo '</tr>';

        echo "<tr class='tab_bg_1'>";
        echo '<td>' . __('Entity') . '</td>';
        echo '<td>';
        Entity::dropdown([
            'name'                => 'entities_id',
            'value'               => $this->fields['entities_id'] ?? 0,
            'display_emptychoice' => true,
        ]);
        echo '</td>';
        echo '<td colspan="2"></td>';
        echo '</tr>';

        $this->showFormButtons($options);

        return true;
    }
}
