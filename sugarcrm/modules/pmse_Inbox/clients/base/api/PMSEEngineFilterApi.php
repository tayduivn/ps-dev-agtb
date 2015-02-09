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

require_once 'clients/base/api/FilterApi.php';

class PMSEEngineFilterApi extends FilterApi
{
    public function registerApiRest()
    {
        return array(
            'filterModuleGet' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Inbox', 'filter'),
                'pathVars' => array('module', ''),
                'method' => 'filterList',
                'jsonParams' => array('filter'),
                'shortHelp' => 'Lists filtered records.',
                'longHelp' => 'include/api/help/module_filter_get_help.html',
                'exceptions' => array(
                    // Thrown in filterList
                    'SugarApiExceptionInvalidParameter',
                    // Thrown in filterListSetup and parseArguments
                    'SugarApiExceptionNotAuthorized',
                ),
            ),
            'filterModuleAll' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Inbox'),
                'pathVars' => array('module'),
                'method' => 'filterList',
                'jsonParams' => array('filter'),
                'shortHelp' => 'List of all records in this module',
                'longHelp' => 'include/api/help/module_filter_get_help.html',
                'exceptions' => array(
                    // Thrown in filterList
                    'SugarApiExceptionInvalidParameter',
                    // Thrown in filterListSetup and parseArguments
                    'SugarApiExceptionNotAuthorized',
                ),
            ),
            'filterModuleAllCount' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Inbox', 'count'),
                'pathVars' => array('module', ''),
                'jsonParams' => array('filter'),
                'method' => 'filterListCount',
                'shortHelp' => 'List of all records in this module',
                'longHelp' => 'include/api/help/module_filter_get_help.html',
                'exceptions' => array(
                    // Thrown in filterListSetup
                    'SugarApiExceptionNotAuthorized',
                ),
            ),
            'filterModuleById' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Inbox', 'filter', '?'),
                'pathVars' => array('module', '', 'record'),
                'method' => 'filterById',
                'shortHelp' => 'Filter records for a module by a predefined filter id.',
                'longHelp' => 'include/api/help/module_filter_record_get_help.html',
                'exceptions' => array(
                    // Thrown in filterById
                    'SugarApiExceptionNotFound',
                    // Thrown in filterList
                    'SugarApiExceptionInvalidParameter',
                    // Thrown in filterListSetup and parseArguments
                    'SugarApiExceptionNotAuthorized',
                ),
            ),
        );
    }

    function __construct()
    {
        parent::__construct();
    }

    public function filterListSetup(ServiceBase $api, array $args, $acl = 'list')
    {
        $seed = BeanFactory::newBean('pmse_BpmFlow');

        if (!$seed->ACLAccess($acl)) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: ' . $args['module']);
        }

        $options = $this->parseArguments($api, $args, $seed);

        // In case the view parameter is set, reflect those fields in the
        // fields argument as well so formatBean only takes those fields
        // into account instead of every bean property.
        if (!empty($args['view'])) {
            $args['fields'] = $options['select'];
        }

        $q = $this->getQueryObjectAux($seed, $options);

        if (!isset($args['filter']) || !is_array($args['filter'])) {
            $args['filter'] = array();
        }

        $this->addFiltersAux($args['filter'], $q->where(), $q);

        if (!empty($args['favorites'])) {
            self::$isFavorite = true;
            self::addFavoriteFilter($q, $q->where(), '_this', 'INNER');
        }
        return array($args, $q, $options, $seed);
    }

    public function addFiltersAux($filters, $where, $q)
    {
        self::addFilters(array(), $where, $q);
        $type = '';
        $isEmpty = empty($filters);
        if (!$isEmpty) {
            foreach ($filters as $filter) {
                foreach ($filter as $field => $values) {
                    if (is_array($values)) {
                        foreach ($values as $condition => $value) {
                            if ($field != 'in_time') {
                                if (is_array($value)) {
                                    $type = $this->applyArrayFilter($where, $condition, $field, $value);
                                } else {
                                    $type = ($field == 'act_assignment_method') ? $value : $type;
                                }
                            }
                        }
                    }
                }
            }
        }

        global $current_user;

        if (strtolower($type) == 'selfservice' || strtolower($type) == 'balanced') {
            $teams = array_keys($current_user->get_my_teams());
            $and = $where->queryAnd();
            $and->in('cas_user_id', $teams);
            $and->isNull('cas_start_date');
            $and->equals('activity_definition.act_assignment_method', 'selfservice');
        }

        if (strtolower($type) == 'static') {
            $and = $where->queryAnd();
            $and->equals('cas_user_id', $current_user->id);
            $or = $and->queryOr();
            $or->equals('activity_definition.act_assignment_method', 'static');
            $or->equals('activity_definition.act_assignment_method', 'balanced');
            $and2 = $or->queryAnd();
            $and2->equals('activity_definition.act_assignment_method', 'selfservice');
            $and2->notNull('cas_start_date');
        }

        if ($isEmpty) {
            $teams = array_keys($current_user->get_my_teams());
            $or = $where->queryOr();
            $and = $or->queryAnd();
            $and->in('cas_user_id', $teams);
            $and->isNull('cas_start_date');
            $and->equals('activity_definition.act_assignment_method', 'selfservice');
            $and2 = $or->queryAnd();
            $and2->equals('cas_user_id', $current_user->id);
            $or2 = $and2->queryOr();
            $or2->equals('activity_definition.act_assignment_method', 'static');
            $or2->equals('activity_definition.act_assignment_method', 'balanced');
            $and3 = $or2->queryAnd();
            $and3->equals('activity_definition.act_assignment_method', 'selfservice');
            $and3->notNull('cas_start_date');
        }
    }



    public function applyArrayFilter($where, $condition, $field, $value)
    {
        $type = '';
        foreach ($value as $val) {
            $type = ($field=='act_assignment_method')? $val : $type;
        }
        return $type;
    }

    public function applyUserFilter($q, $condition, $field, $value)
    {
        if ($condition == '$equals' && $field == 'user_disabled' && $value==1) {
            $q->joinTable('users', array('alias' => 'users', 'joinType' => 'INNER', 'linkingTable' => true))
                ->on()
                ->equalsField('users.id', 'assigned_user_id')
                ->notEquals('users.employee_status', 'Active');
        }
    }

    private function getQueryObjectAux(SugarBean $seed, array $options)
    {
        if (empty($options['select'])) {
            $options['select'] = self::$mandatory_fields;
        }
        $queryOptions = array('add_deleted' => (!isset($options['add_deleted'])||$options['add_deleted'])?true:false);
        if ($queryOptions['add_deleted'] == false) {
            $options['select'][] = 'deleted';
        }

        $q = new SugarQuery();
        $q->from($seed, $queryOptions);
        $q->distinct(false);
        $fields = array();
        foreach ($options['select'] as $field) {
            // fields that aren't in field defs are removed, since we don't know
            // what to do with them
            if (!empty($seed->field_defs[$field])) {
                // Set the field into the field list
                $fields[] = $field;
            }
        }

        //INNER JOIN BPM INBOX TABLE
        $fields[] = array("date_entered", 'date_entered');
        $fields[] = array("cas_id", 'cas_id');
        $fields[] = array("cas_sugar_module", 'cas_sugar_module');


        $q->joinTable('pmse_inbox', array('alias' => 'inbox', 'joinType' => 'INNER', 'linkingTable' => true))
            ->on()
            ->equalsField('inbox.cas_id', 'cas_id')
            ->equals('inbox.deleted', 0);
        $fields[] = array("inbox.id", 'inbox_id');
        $fields[] = array("inbox.cas_title", 'cas_title');
        $q->where()
            ->equals('cas_flow_status', 'FORM');

        //INNER JOIN BPMN ACTIVITY DEFINITION
        $q->joinTable('pmse_bpmn_activity', array('alias' => 'activity', 'joinType' => 'INNER', 'linkingTable' => true))
            ->on()
            ->equalsField('activity.id', 'bpmn_id')
            ->equals('activity.deleted', 0);
        $fields[] = array("activity.name", 'act_name');

        //INNER JOIN BPMN ACTIVITY DEFINTION
        $q->joinTable('pmse_bpm_activity_definition', array('alias' => 'activity_definition', 'joinType' => 'INNER', 'linkingTable' => true))
            ->on()
            ->equalsField('activity_definition.id', 'activity.id')
            ->equals('activity_definition.deleted', 0);
        $fields[] = array("activity_definition.act_assignment_method", 'act_assignment_method');

        //INNER JOIN BPMN PROCESS DEFINTION
        $q->joinTable('pmse_bpmn_process', array('alias' => 'process', 'joinType' => 'INNER', 'linkingTable' => true))
            ->on()
            ->equalsField('process.id', 'inbox.pro_id')
            ->equals('process.deleted', 0);
        $fields[] = array("process.name", 'pro_title');

        //INNER JOIN USER_DATA DEFINTION
        $q->joinTable('users', array('alias' => 'user_data', 'joinType' => 'LEFT', 'linkingTable' => true))
            ->on()
            ->equalsField('user_data.id', 'cas_user_id')
            ->equals('user_data.deleted', 0);
        $fields[] = array("user_data.first_name", 'first_name');
        $fields[] = array("user_data.last_name", 'last_name');

        //INNER JOIN TEAM_DATA DEFINTION
        $q->joinTable('teams', array('alias' => 'team_data', 'joinType' => 'LEFT', 'linkingTable' => true))
            ->on()
            ->equalsField('team_data.id', 'cas_user_id')
            ->equals('team_data.deleted', 0);
        $fields[] = array("team.name", 'team_name');

        $q->select($fields)
            ->fieldRaw('CONCAT(COALESCE(user_data.first_name, ""), " ", user_data.last_name)', 'assigned_user_name');

        foreach ($options['order_by'] as $orderBy) {
            if ($orderBy[0] == 'pro_title'){
                $orderBy[0] = 'process.name';
            }
            if ($orderBy[0] == 'task_name'){
                $orderBy[0] = 'activity.name';
            }
            if ($orderBy[0] == 'cas_title'){
                $orderBy[0] = 'inbox.cas_title';
            }
            $q->orderBy($orderBy[0], $orderBy[1]);
        }
        // Add an extra record to the limit so we can detect if there are more records to be found
        $q->limit($options['limit'] + 1);
        $q->offset($options['offset']);

        return $q;
    }

    protected function formatBeans(ServiceBase $api, $args, $beans)
    {
        if (!empty($args['fields']) && !is_array($args['fields'])) {
            $args['fields'] = explode(',',$args['fields']);
        }

        $ret = array();

        foreach ($beans as $bean) {
            if (!is_subclass_of($bean, 'SugarBean')) {
                continue;
            }
            $arr_aux = array();
            $arr_aux['cas_id'] = (isset($bean->fetched_row['cas_id']))? $bean->fetched_row['cas_id']:$bean->fetched_row['pmse_bpm_flow__cas_id'];
            $arr_aux['act_assignment_method'] = $bean->fetched_row['act_assignment_method'];
            $arr_aux['cas_title'] = $bean->fetched_row['cas_title'];
            $arr_aux['pro_title'] = $bean->fetched_row['pro_title'];
            $arr_aux['date_entered'] = $bean->fetched_row['date_entered'];
            $arr_aux['name'] = $bean->fetched_row['cas_title'];
            $arr_aux['cas_create_date'] = $bean->fetched_row['date_entered'];
            $arr_aux['flow_id'] = $bean->fetched_row['id'];
            $arr_aux['id2'] = $bean->fetched_row['inbox_id'];
            $arr_aux['task_name'] = $bean->fetched_row['act_name'];
            $arr_aux['cas_status'] = $bean->fetched_row['act_assignment_method'];
            $arr_aux['assigned_user_name'] = $bean->fetched_row['assigned_user_name'];
            $arr_aux['cas_sugar_module'] = $bean->fetched_row['cas_sugar_module'];
            $arr_aux['in_time'] = true;
            $arr_aux['id'] = $bean->fetched_row['inbox_id'];
            $ret[] = array_merge($this->formatBean($api, $args, $bean), $arr_aux);
        }

        return $ret;
    }

    protected function getOrderByFromArgs(array $args, SugarBean $seed = null)
    {
        $orderBy = array();
        if (!isset($args['order_by']) || !is_string($args['order_by'])) {
            return $orderBy;
        }

        $columns = explode(',', $args['order_by']);
        $parsed = array();
        foreach ($columns as $column) {
            $column = explode(':', $column, 2);
            $field = array_shift($column);

            // do not override previous value if it exists since it should have higher precedence
            if (!isset($parsed[$field])) {
                $direction = array_shift($column);
                $parsed[$field] = strtolower($direction) !== 'desc';
            }
        }

        $converted = array();
        foreach ($parsed as $field => $direction) {
            $converted[] = array($field, $direction ? 'ASC' : 'DESC');
        }

        return $converted;
    }
}
