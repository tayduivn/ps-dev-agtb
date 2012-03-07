<?php
class OwnedByMe
{
    protected  $bean;
    public function __construct($bean)
    {
        $this->bean = $bean;
        $this->module_dir = $this->bean->module_dir;
    }

    public function addVisibilityClause(&$query)
    {
        global $current_user;
        $query .= " INNER JOIN (SELECT id FROM {$this->bean->table_name} WHERE {$this->bean->table_name}.assigned_user_id='{$current_user->id}') mememe ON mememe.id={$this->bean->table_name}.id";
    }
}
