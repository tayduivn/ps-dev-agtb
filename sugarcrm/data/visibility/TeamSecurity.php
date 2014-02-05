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
        global $current_user;

        // Support portal will never respect Teams, even if they do earn more than them even while raising the teamsets
        if(isset($_SESSION['type'])&&$_SESSION['type']=='support_portal') {
            return;
        }

        if($this->isTeamSecurityApplicable())
        {
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
            $team_table_alias = DBManagerFactory::getInstance()->getValidDBName($team_table_alias, 'alias');
            if ($this->getOption('as_condition')) {
                $query .= " AND {$table_alias}.team_set_id IN (select tst.team_set_id from team_sets_teams tst
                          INNER JOIN team_memberships {$team_table_alias} ON tst.team_id = {$team_table_alias}.team_id
                          AND {$team_table_alias}.user_id = '$current_user_id'
                          AND {$team_table_alias}.deleted=0)";
            } else {
                $query .= " INNER JOIN (select tst.team_set_id from team_sets_teams tst";
                $query .= " INNER JOIN team_memberships {$team_table_alias} ON tst.team_id = {$team_table_alias}.team_id
                                    AND {$team_table_alias}.user_id = '$current_user_id'
                                    AND {$team_table_alias}.deleted=0 group by tst.team_set_id) {$table_alias}_tf
                                    on {$table_alias}_tf.team_set_id  = {$table_alias}.team_set_id ";
                if ($this->getOption('join_teams')) {
                    $query .= " INNER JOIN teams ON teams.id = team_memberships.team_id AND teams.deleted=0 ";
                }
            }
        }
    }

    /**
     * Add Visibility to a SugarQuery Object
     * @param SugarQuery $sugarQuery
     * @param array $options
     * @return string|SugarQuery
     */
    public function addVisibilityFromQuery(SugarQuery $sugarQuery, $options = array())
    {
        if($this->getOption('as_condition')) {
            $table_alias = $this->getOption('table_alias');
            if(empty($sugarQuery->join[$table_alias])) {
                return;
            }
            $join = $sugarQuery->join[$table_alias];
            $join->query = $sugarQuery;
            $add_join = '';
            $this->addVisibilityFrom($add_join, $options);
            if(!empty($add_join)) {
                if(substr($add_join, 0, 5) == " AND ") {
                    $add_join = substr($add_join, 5);
                }
                $join->on()->queryAnd()->addRaw($add_join);
            }
        } else {
            $join = '';
            $this->addVisibilityFrom($join, $options);
            if(!empty($join)) {
                $sugarQuery->joinRaw($join);
            }
        }
        return $sugarQuery;
    }

    /*
     * Get sugar search engine definitions
     * @param string $engine search engine name
     * @return array
     * Called before the bean is indexed so that any calculated attributes can updated.
     * Since the team security id is updated directly, there is no need to implement anything custom
     */
    public function beforeSseIndexing()
    {
    }

    public function addSseVisibilityFilter($engine, $filter)
    {
        if($this->isTeamSecurityApplicable())
        {
            if($engine instanceof SugarSearchEngineElastic) {
                $filter->addMust($engine->getTeamTermFilter());
            }
        }
        return $filter;
    }

    /**
     * Verifies if team security needs to be applied
     * @return bool true if team security needs to be applied
     */
    protected function isTeamSecurityApplicable()
    {
        global $current_user;

        if( $this->bean->module_dir == 'WorkFlow'  // copied from old team security clause
            || $this->bean->disable_row_level_security
            || (!empty($current_user) && $current_user->isAdminForModule($this->module_dir))
        ) return false;

        // Note that if the $current_user is not set we still apply team security
        // This does not make any sense by itself as the result will always be negative (no access)
        return true;
    }
}
