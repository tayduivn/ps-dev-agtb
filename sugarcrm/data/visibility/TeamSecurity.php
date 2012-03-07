<?php

/**
 * Team security visibility
 */
class TeamSecurity extends SugarVisibility
{
    public function addVisibilityClause(&$query)
    {
        if(!$this->bean->disable_row_level_security) {
            // We need to confirm that the user is a member of the team of the item.

            global $current_user;
            // The user either has to be an admin, or be assigned to the team that owns the data
            $team_table_alias = 'team_memberships';
            $table_alias = $this->bean->table_name;

            if ( !$current_user->isAdminForModule($this->module_dir) && $this->module_dir != 'WorkFlow') {
                $query .= " INNER JOIN (select tst.team_set_id from team_sets_teams tst ";
                $query .= " INNER JOIN team_memberships {$team_table_alias} ON tst.team_id = {$team_table_alias}.team_id
                                    AND {$team_table_alias}.user_id = '$current_user->id'
                                    AND {$team_table_alias}.deleted=0 group by tst.team_set_id) {$table_alias}_tf on {$table_alias}_tf.team_set_id  = {$table_alias}.team_set_id ";
            }
        }
    }
}