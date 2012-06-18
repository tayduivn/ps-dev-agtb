<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
//FILE SUGARCRM flav=pro ONLY

/**
 * Team security visibility
 */
class TeamSecurity extends SugarVisibility
{
    public function addVisibilityFrom(&$query)
    {
        // Support portal will never respect Teams, even if they do earn more than them even while raising the teamsets
        if(isset($_SESSION['type'])&&$_SESSION['type']=='support_portal') {
            return;
        }


        // copied from old team security clause
        if($this->bean->module_dir == 'WorkFlow') return;
        if(!$this->bean->disable_row_level_security) {
            // We need to confirm that the user is a member of the team of the item.

            global $current_user;
            if(empty($current_user)) {
                $current_user_id = '';
            } else {
                $current_user_id = $current_user->id;
            }
            // The user either has to be an admin, or be assigned to the team that owns the data
            $team_table_alias = 'team_memberships';
            $table_alias = $this->getOption('table_alias');
            if(!empty( $table_alias)) {
                $team_table_alias .= $table_alias;
            } else {
                $table_alias = $this->bean->table_name;
            }

            if ((empty($current_user) || !$current_user->isAdminForModule($this->module_dir)) && $this->module_dir != 'WorkFlow') {
                $query .= "INNER JOIN (select tst.team_set_id from team_sets_teams tst";
                $query .= " INNER JOIN team_memberships {$team_table_alias} ON tst.team_id = {$team_table_alias}.team_id
                                    AND {$team_table_alias}.user_id = '$current_user_id'
                                    AND {$team_table_alias}.deleted=0 group by tst.team_set_id) {$table_alias}_tf on {$table_alias}_tf.team_set_id  = {$table_alias}.team_set_id ";
            }
            if($this->getOption('join_teams')) {
                $query .= " INNER JOIN teams ON teams.id = team_memberships.team_id AND teams.deleted=0 ";
            }
        }
    }
}