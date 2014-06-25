<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
//FILE SUGARCRM flav=pro ONLY

/**
 * Team security visibility
 */
class TeamSecurity extends SugarVisibility
{

    /**
     * Get team security join as a IN() condition
     * @param string $current_user_id
     * @return string
     */
    protected function getCondition($current_user_id)
    {
        $team_table_alias = 'team_memberships';
        $table_alias = $this->getOption('table_alias');
        if(!empty($table_alias)) {
            $team_table_alias = $this->bean->db->getValidDBName($team_table_alias.$table_alias, true, 'table');
        } else {
            $table_alias = $this->bean->table_name;
        }
        return " {$table_alias}.team_set_id IN (select tst.team_set_id from team_sets_teams tst
                              INNER JOIN team_memberships {$team_table_alias} ON tst.team_id = {$team_table_alias}.team_id
                              AND {$team_table_alias}.user_id = '$current_user_id'
                              AND {$team_table_alias}.deleted=0)";
    }

    /**
     * Get team security as a JOIN clause
     * @param string $current_user_id
     * @return string
     */
    protected function getJoin($current_user_id)
    {
        $team_table_alias = 'team_memberships';
        $table_alias = $this->getOption('table_alias');
        if(!empty($table_alias)) {
            $team_table_alias = $this->bean->db->getValidDBName($team_table_alias.$table_alias, true, 'table');
        } else {
            $table_alias = $this->bean->table_name;
        }
        $query = " INNER JOIN (select tst.team_set_id from team_sets_teams tst";
        $query .= " INNER JOIN team_memberships {$team_table_alias} ON tst.team_id = {$team_table_alias}.team_id
                    AND {$team_table_alias}.user_id = '$current_user_id'
                    AND {$team_table_alias}.deleted=0 group by tst.team_set_id) {$table_alias}_tf on {$table_alias}_tf.team_set_id  = {$table_alias}.team_set_id ";
        if($this->getOption('join_teams')) {
            $query .= " INNER JOIN teams ON teams.id = {$team_table_alias}.team_id AND teams.deleted=0 ";
        }
        return $query;
    }

    /**
     * Check if we need WHERE condition
     * @return boolean
     */
    protected function useCondition()
    {
        return $this->getOption('as_condition') || $this->getOption('where_condition');
    }

    public function addVisibilityFrom(&$query)
    {
        // We'll get it on where clause
        if($this->getOption('where_condition')) {
            return;
        }
        $this->addVisibility($query);

        return $query;
    }

    public function addVisibilityWhere(&$query)
    {
        if(!$this->getOption('where_condition')) {
            return;
        }
        $this->addVisibility($query);

        return $query;
    }

    /**
     * Add visibility query
     * @param string $query
     */
    protected function addVisibility(&$query)
    {
        // Support portal will never respect Teams, even if they do earn more than them even while raising the teamsets
        if(isset($_SESSION['type'])&&$_SESSION['type']=='support_portal') {
            return;
        }

        if(!$this->isTeamSecurityApplicable()) {
            return;
        }

        $current_user_id = empty($GLOBALS['current_user'])?'':$GLOBALS['current_user']->id;

        if($this->useCondition()) {
            $cond = $this->getCondition($current_user_id);
            if($query) {
                $query .= " AND ".ltrim($cond);
            } else {
                $query = $cond;
            }
        } else {
            $query .= $this->getJoin($current_user_id);
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
       // We'll get it on where clause
       if($this->getOption('where_condition')) {
           return;
       }

       if($this->useCondition()) {
           $table_alias = $this->getOption('table_alias');
           if(empty($sugarQuery->join[$table_alias])) {
               return;
           }
           $join = $sugarQuery->join[$table_alias];
           $join->query = $sugarQuery;
           $add_join = '';
           $this->addVisibility($add_join);
           if(!empty($add_join)) {
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

   /**
    * Add Visibility to a SugarQuery Object
    * @param SugarQuery $sugarQuery
    * @param array $options
    * @return string|SugarQuery
    */
   public function addVisibilityWhereQuery(SugarQuery $sugarQuery, $options = array())
   {
       if(!$this->getOption('where_condition')) {
           return;
       }
       $cond = '';
       $this->addVisibility($cond);
       if(!empty($cond)) {
          $sugarQuery->whereRaw($cond);
       }
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
}