<?php

/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'include/api/SugarApi.php';
require_once 'include/SugarQuery/SugarQuery.php';
require_once 'data/Relationships/RelationshipFactory.php';

class FilterApi extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            'filterModuleGet' => array(
                'reqType' => 'GET',
                'path' => array('<module>', 'filter'),
                'pathVars' => array('module', ''),
                'method' => 'filterList',
                'jsonParams' => array('filter'),
                'shortHelp' => 'Lists filtered records.',
                'longHelp' => 'include/api/help/module_filter_get_help.html',
            ),
            'filterModuleAll' => array(
                'reqType' => 'GET',
                'path' => array('<module>'),
                'pathVars' => array('module'),
                'method' => 'filterList',
                'shortHelp' => 'List of all records in this module',
                'longHelp' => 'include/api/help/module_filter_get_help.html',
            ),
            'filterModulePost' => array(
                'reqType' => 'POST',
                'path' => array('<module>', 'filter'),
                'pathVars' => array('module', ''),
                'method' => 'filterList',
                'shortHelp' => 'Lists filtered records.',
                'longHelp' => 'include/api/help/module_filter_post_help.html',
            ),
            'filterModuleById' => array(
                'reqType' => 'GET',
                'path' => array('<module>', 'filter', '?'),
                'pathVars' => array('module', '', 'record'),
                'method' => 'filterById',
                'shortHelp' => 'Filter records for a module by a predefined filter id.',
                'longHelp' => 'include/api/help/module_filter_record_get_help.html',
            ),
            'filterRelatedRecords' => array(
                'reqType' => 'GET',
                'path' => array('<module>', '?', 'link', '?', 'filter'),
                'pathVars' => array('module', 'record', '', 'link_name', ''),
                'jsonParams' => array('filter'),
                'method' => 'filterRelated',
                'shortHelp' => 'Lists related filtered records.',
                'longHelp' => 'include/api/help/module_record_link_link_name_filter_get_help.html',

            ),
        );
    }

    protected $defaultLimit = 20; // How many records should we show if they don't pass up a limit

    protected static $current_user;

    // id and date_modified should always be in the response
    // user fields are needed for ACLs since they check owners
    protected static $mandatory_fields = array(
        'id',
        'date_modified',
        'assigned_user_id',
        'created_by'
    );

    public function __construct()
    {
        global $current_user;
        self::$current_user = $current_user;
    }

    public function filterById(ServiceBase $api, array $args)
    {
        $filter = BeanFactory::getBean('Filters', $args['record']);
        $filter_definition = json_decode($filter->filter_definition, true);
        $args = array_merge($args, $filter_definition);
        unset($args['record']);
        return $this->filterList($api, $args);
    }

    protected function parseArguments(ServiceBase $api, array $args, SugarBean $seed = null)
    {
        $options = array();

        // Set up the defaults
        $options['limit'] = $this->defaultLimit;
        $options['offset'] = 0;
        $options['order_by'] = array(array('date_modified', 'DESC'));

        if (!empty($args['max_num'])) {
            $options['limit'] = (int) $args['max_num'];
        }
        if (!empty($args['offset'])) {
            if ($args['offset'] == 'end') {
                $options['offset'] = 'end';
            } else {
                $options['offset'] = (int) $args['offset'];
            }
        }
        if (!empty($args['order_by']) && !empty($seed)) {
            $orderBys = explode(',', $args['order_by']);
            $orderByArray = array();
            foreach ($orderBys as $order) {
                $orderSplit = explode(':', $order);

                if (!$seed->ACLFieldAccess($orderSplit[0], 'list')
                    || !isset($seed->field_defs[$orderSplit[0]])
                ) {
                    throw new SugarApiExceptionNotAuthorized(
                        sprintf('No access to view field: %s in module: %s', $orderSplit[0], $args['module'])
                    );
                }

                if (!isset($orderSplit[1]) || strtolower($orderSplit[1]) == 'desc') {
                    $orderSplit[1] = 'DESC';
                } else {
                    $orderSplit[1] = 'ASC';
                }
                $orderByArray[] = $orderSplit;
            }
            $options['order_by'] = $orderByArray;
        }

        // Set $options['module'] so that runQuery can create beans of the right
        // type.
        if (!empty($seed)) {
            $options['module'] = $seed->module_name;
        }


        //Set the list of fields to be used in the select.
        $options['select'] = !empty($args['fields']) ? explode(",", $args['fields']) : array();

        //Force id and date_modified into the select
        $options['select'] = array_unique(
            array_merge($options['select'], self::$mandatory_fields)
        );

        return $options;
    }

    public function filterList(ServiceBase $api, array $args)
    {
        if ( !empty($args['q']) ) {
            // We need to use unified search for this for compatibilty with Nomad
            require_once('clients/base/api/UnifiedSearchApi.php');
            $search = new UnifiedSearchApi();
            $args['module_list'] = $args['module'];
            return $search->globalSearch($api, $args);
        }

        $seed = BeanFactory::newBean($args['module']);

        if (!$seed->ACLAccess('list')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: ' . $args['module']);
        }

        $options = $this->parseArguments($api, $args, $seed);

        $q = self::getQueryObject($seed, $options);

        // return $args['filter'];
        if (!isset($args['filter']) || !is_array($args['filter'])) {
            $args['filter'] = array();
        }
        self::addFilters($args['filter'], $q->where(), $q);

        if (!empty($args['my_items'])) {
            self::addOwnerFilter($q, $q->where(), '_this');
        }

        if (!empty($args['favorites'])) {
            self::addFavoriteFilter($q, $q->where(), '_this');
        }

        $api->action = 'list';

        return $this->runQuery($api, $args, $q, $options, $seed);
    }

    public function filterRelated(ServiceBase $api, array $args)
    {
        // Load the parent bean.
        $record = BeanFactory::retrieveBean($args['module'], $args['record']);

        if (empty($record)) {
            throw new SugarApiExceptionNotFound(
                sprintf(
                    'Could not find parent record %s in module: %s',
                    $args['record'],
                    $args['module']
                )
            );
        }
        if (!$record->ACLAccess('view')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: ' . $args['module']);
        }

        // Load the relationship.
        $linkName = $args['link_name'];
        if (!$record->load_relationship($linkName)) {
            // The relationship did not load.
            throw new SugarApiExceptionNotFound('Could not find a relationship named: ' . $args['link_name']);
        }
        $linkModuleName = $record->$linkName->getRelatedModuleName();
        $linkSeed = BeanFactory::getBean($linkModuleName);
        if (!$linkSeed->ACLAccess('list')) {
            throw new SugarApiExceptionNotAuthorized('No access to list records for module: ' . $linkModuleName);
        }

        $rf = SugarRelationshipFactory::getInstance();
        $relObj = $record->$linkName->getRelationshipObject();
        $relDef = $rf->getRelationshipDef($relObj->name);
        $tableName = $record->$linkName->getRelatedModuleLinkName();

        if ($record->$linkName->getSide() == REL_LHS) {
            $column = $relDef['lhs_key'];
        } else {
            $column = $relDef['rhs_key'];
        }

        $options = $this->parseArguments($api, $args, $linkSeed);
        $q = self::getQueryObject($linkSeed, $options);
        if (!isset($args['filter']) || !is_array($args['filter'])) {
            $args['filter'] = array();
        }
        $args['filter'][][$tableName . '.' . $column] = array('$equals' => $record->id);
        self::addFilters($args['filter'], $q->where(), $q);

        $api->action = 'list';

        return $this->runQuery($api, $args, $q, $options, $linkSeed);
    }

    protected static function getQueryObject(SugarBean $seed, array $options)
    {
        $q = new SugarQuery();
        // Just need ID, we need to fetch beans so we can format them later.
        $q->from($seed);
        if (empty($options['select'])) {
            $options['select'] = self::$mandatory_fields;
        }

        $fields = array();
        foreach ($options['select'] as $field) {
            // FIXME: convert this to vardefs too?
            //BEGIN SUGARCRM flav=pro ONLY
            if ($field == 'my_favorite') {
                $fjoin = $q->join("favorites");
                $fields[] = array($fjoin->joinName() . ".id", 'my_favorite');
                continue;
            }
            //END SUGARCRM flav=pro ONLY

            // fields that aren't in field defs are removed, since we don't know what to do with them
            if (!empty($seed->field_defs[$field])) {
                $fields[] = $field;
            }
        }
        $q->select($fields);
        $q->distinct(true);

        foreach ($options['order_by'] as $orderBy) {
            self::verifyField($q, $orderBy[0]);
            $q->orderBy($orderBy[0], $orderBy[1]);
        }
        // Add an extra record to the limit so we can detect if there are more records to be found
        $q->limit($options['limit'] + 1);
        $q->offset($options['offset']);

        return $q;
    }


    /**
     * Populate related beans from data array.
     *
     * @param SugarBean $bean
     * @param array $data
     */
    protected function populateRelatedFields($bean, $data)
    {
        $relates = array();
        // fill in related rows data by field
        foreach ($data as $key => $value) {
            if (($split = strpos($key, "__")) > 0) {
                $relates[substr($key, 0, $split)][] = substr($key, $split + 2);
            }
        }

        foreach ($bean->field_defs as $field => $fieldDef) {
            if ($fieldDef['type'] == 'relate' && !empty($fieldDef['link'])) {
                if (empty($data[$field]) && empty($relates[$field])) {
                    continue;
                }

                $rbean = $bean->getRelatedBean($fieldDef['link']);
                if (empty($rbean)) {
                    continue;
                }

                if (!empty($data[$field])) {
                    // we have direct data - populate it
                    $rbean->populateFromRow(
                        array($fieldDef['rname'] => $data[$field]),
                        true
                    );
                } else {
                    if (empty($relates[$field])) {
                        continue;
                    }

                    $reldata = array();
                    foreach ($relates[$field] as $relfield) {
                        $reldata[$relfield] = $data["{$field}__{$relfield}"];
                    }
                    if (!empty($reldata)) {
                        $rbean->populateFromRow($reldata, true);
                    }
                }

                if (empty($rbean->id) && !empty($fieldDef['id_name']) && !empty($data[$fieldDef['id_name']])) {
                    $rbean->id = $data[$fieldDef['id_name']];
                }

            }
        }
        // Call some data fillings for the bean
        foreach ($bean->related_beans as $rbean) {
            if (empty($rbean->id)) {
                continue;
            }

            $rbean->check_date_relationships_load();
            // $rbean->fill_in_additional_list_fields();
            if ($rbean->hasCustomFields()
            ) {
                $rbean->custom_fields->fill_relationships();
            }
            $rbean->call_custom_logic("process_record");
        }
        //
    }

    protected function runQuery(
        ServiceBase $api,
        array $args,
        SugarQuery $q,
        array $options,
        SugarBean $seed
    ) {
        $seed->call_custom_logic("before_filter", array($q, $options));
        $GLOBALS['log']->info("Filter SQL: " . $q->compileSql());
        $idRows = $q->execute();
        // return $idRows;

        $data = array();
        $data['next_offset'] = -1;

        $beans = $bean_ids = array();
        foreach ($idRows as $i => $row) {
            if ($i == $options['limit']) {
                $data['next_offset'] = (int) ($options['limit'] + $options['offset']);
                continue;
            }
            if (empty($args['fields'])) {
                //FIXME: Without a field list, we need to just do a full retrieve to make sure we get the entire bean.
                $bean = BeanFactory::getBean($options['module'], $row['id']);
            } else {
                $bean = clone $seed;
                // convert will happen inside populateFromRow
                $bean->loadFromRow($row, true);
                $this->populateRelatedFields($bean, $row);
                if (!empty($bean->id) && !empty($row['parent_type']) && $q->hasParent(
                )
                ) {
                    $child_info[$row['parent_type']][] = array(
                        'child_id' => $bean->id,
                        'parent_id' => $bean->parent_id,
                        'parent_type' => $bean->parent_type,
                        'type' => 'parent'
                    );
                }
            }
            if ($bean && !empty($bean->id)) {
                $beans[$bean->id] = $bean;
                $bean_ids[] = $bean->id;
            }
        }
        /* FIXME: this is a hack for emails, think about how to fix it */
        if (!empty($bean_ids) && isset($seed->field_defs['email']) && in_array(
            'email',
            $options['select']
        )
        ) {
            $email = BeanFactory::getBean('EmailAddresses');
            $q = $email->getEmailsQuery($seed->module_name);
            $q->where()->in("ear.bean_id", $bean_ids);
            $q->select->field("ear.bean_id");
            $email_rows = $q->execute();
            foreach ($email_rows as $email) {
                $beans[$email['bean_id']]->emailData[] = $email;
            }
        }
        // Load parent records
        if (!empty($child_info)) {
            $parent_beans = $seed->retrieve_parent_fields($child_info);
            foreach ($parent_beans as $id => $parent_data) {
                unset($parent_data['id']);
                foreach ($parent_data as $field => $value) {
                    $beans[$id]->$field = $value;
                }
            }
        }

        $data['records'] = $this->formatBeans($api, $args, $beans);

        return $data;
    }

    /**
     * Verify that the passed field is correct
     * @param SugarQuery $q
     * @param string $field
     * @return bool
     * @throws SugarApiExceptionInvalidParameter
     */
    protected static function verifyField(SugarQuery $q, $field)
    {
        if (strpos($field, '.')) {
            // It looks like it's a related field that it's searching by
            list($relatedTable, $field) = explode('.', $field);

            $q->from->load_relationship($relatedTable);
            if(empty($q->from->$relatedTable)) {
                throw new SugarApiExceptionInvalidParameter("Invalid link $relatedTable for field $field");
            }

            if($q->from->$relatedTable->getType() == "many") {
                // FIXME: we have a problem here: we should allow 'many' links for related to match against parent object
                // but allowing 'many' in  other links may lead to duplicates. So for now we allow 'many'
                // but we should figure out how to find if 'many' is permittable or not.
                // throw new SugarApiExceptionInvalidParameter("Cannot use condition against multi-link $relatedTable");
            }

            $q->join($relatedTable, array('joinType' => 'LEFT'));

            $bean = $q->getTableBean($relatedTable);
            if(empty($bean)) {
                throw new SugarApiExceptionInvalidParameter("Cannot use condition against $relatedTable - unknown module");
            }

        } else {
            $bean = $q->from;
        }
        $defs = $bean->field_defs;

        if(empty($defs[$field])) {
            throw new SugarApiExceptionInvalidParameter("Unknown field $field");
        }

        if(!$bean->ACLFieldAccess($field)) {
            throw new SugarApiExceptionNotAuthorized("Access for field $field is not allowed");
        }

        $field_def = $defs[$field];
        if(empty($field_def['source']) || $field_def['source'] == 'db' || $field_def['source'] == 'custom_field') {
            return true;
        }

        if($field_def['source'] == 'relate') {
            $relfield = $field_def['rname'];
            $link = $field_def['link'];
            return self::verifyField($q, "$link.$relfield");
        }

        return true;
    }

    /**
     * Add filters to the query
     * @param array $filterDefs
     * @param SugarQuery_Builder_Where $where
     * @param SugarQuery $q
     * @throws SugarApiExceptionInvalidParameter
     */
    protected static function addFilters(
        array $filterDefs,
        SugarQuery_Builder_Where $where,
        SugarQuery $q
    ) {
        foreach ($filterDefs as $filterDef) {
            if (!is_array($filterDef)) {
                throw new SugarApiExceptionInvalidParameter(
                    sprintf(
                        'Did not recognize the definition: %s',
                        print_r($filterDef, true)
                    )
                );
            }
            foreach ($filterDef as $field => $filter) {
                if ($field == '$or') {
                    self::addFilters($filter, $where->queryOr(), $q);
                } elseif ($field == '$and') {
                    self::addFilters($filter, $where->queryAnd(), $q);
                } elseif ($field == '$favorite') {
                    self::addFavoriteFilter($q, $where, $filter);
                } elseif ($field == '$owner') {
                    self::addOwnerFilter($q, $where, $filter);
                } elseif ($field == '$creator') {
                    self::addCreatorFilter($q, $where, $filter);
                } elseif ($field == '$tracker') {
                    self::addTrackerFilter($q, $where, $filter);
                } else {
                    // Looks like just a normal field, parse it's options
                    self::verifyField($q, $field);

                    if (!is_array($filter)) {
                        // This is just simple match
                        $where->equals($field, $filter);
                        continue;
                    }
                    foreach ($filter as $op => $value) {
                        switch ($op) {
                            case '$equals':
                                $where->equals($field, $value);
                                break;
                            case '$not_equals':
                                $where->notEquals($field, $value);
                                break;
                            case '$starts':
                                $where->starts($field, $value);
                                break;
                            case '$ends':
                                $where->ends($field, $value);
                                break;
                            case '$contains':
                                $where->contains($field, $value);
                                break;
                            case '$in':
                                if (!is_array($value)) {
                                    throw new SugarApiExceptionInvalidParameter('$in requires an array');
                                }
                                $where->in($field, $value);
                                break;
                            case '$not_in':
                                if (!is_array($value)) {
                                    throw new SugarApiExceptionInvalidParameter('$not_in requires an array');
                                }
                                $where->notIn($field, $value);
                                break;
                            case '$between':
                                if (!is_array($value) || count($value) != 2) {
                                    throw new SugarApiExceptionInvalidParameter(
                                        '$between requires an array with two values.'
                                    );
                                }
                                $where->between($field, $value[0], $value[1]);
                                break;
                            case '$is_null':
                                $where->isNull($field);
                                break;
                            case '$not_null':
                                $where->notNull($field);
                                break;
                            case '$lt':
                                $where->lt($field, $value);
                                break;
                            case '$lte':
                                $where->lte($field, $value);
                                break;
                            case '$gt':
                                $where->gt($field, $value);
                                break;
                            case '$gte':
                                $where->gte($field, $value);
                                break;
                            case '$dateRange':
                                $where->dateRange($field, $value);
                                break;
                            case '$dateBetween':
                                if (!is_array($value) || count($value) != 2) {
                                    throw new SugarApiExceptionInvalidParameter(
                                        '$dateBetween requires an array with two values.'
                                    );
                                }
                                $where->dateBetween($field, $value);
                                break;
                            default:
                                throw new SugarApiExceptionInvalidParameter("Did not recognize the operand: " . $op);
                        }
                    }
                }
            }
        }
    }

    /**
     * This function adds an owner filter to the sugar query
     *
     * @param SugarQuery $q The whole SugarQuery object
     * @param SugarQuery_Builder_Where $where The Where part of the SugarQuery object
     * @param string $link Which module are you adding the owner filter to.
     */
    protected static function addOwnerFilter(
        SugarQuery $q,
        SugarQuery_Builder_Where $where,
        $link
    ) {
        if ($link == '' || $link == '_this') {
            $linkPart = '';
        } else {
            $q->join($link, array('joinType' => 'LEFT'));
            $linkPart = $link . '.';
        }

        $where->equals($linkPart . 'assigned_user_id', self::$current_user->id);
    }

    /**
     * This function adds a creator filter to the sugar query
     *
     * @param SugarQuery $q The whole SugarQuery object
     * @param SugarQuery_Builder_Where $where The Where part of the SugarQuery object
     * @param string $link Which module are you adding the owner filter to.
     */
    protected static function addCreatorFilter(
        SugarQuery $q,
        SugarQuery_Builder_Where $where,
        $link
    ) {
        if ($link == '' || $link == '_this') {
            $linkPart = '';
        } else {
            $q->join($link, array('joinType' => 'LEFT'));
            $linkPart = $link . '.';
        }

        $where->equals($linkPart . 'created_by', self::$current_user->id);
    }

    /**
     * This function adds a favorite filter to the sugar query
     *
     * @param SugarQuery $q The whole SugarQuery object
     * @param SugarQuery_Builder_Where $where The Where part of the SugarQuery object
     * @param string $link Which module are you adding the favorite filter to.
     */
    protected static function addFavoriteFilter(
        SugarQuery $q,
        SugarQuery_Builder_Where $where,
        $link
    ) {
        $sfOptions = array('joinType' => 'LEFT', 'favorites' => true);
        if ($link == '' || $link == '_this') {
            $link_name = 'favorites';
        } else {
            $q->join($link, array('joinType' => 'LEFT'));
            $sfOptions['joinTo'] = $link;
            $link_name = "sf_".$link;
        }

        $fjoin = $q->join($link_name, $sfOptions);

        $where->notNull($fjoin->joinName() . '.id');
    }

    protected static function addTrackerFilter(
        SugarQuery $q,
        SugarQuery_Builder_Where $where,
        $interval
    ) {
        // FIXME: FRM-226, logic for these needs to be moved to SugarQuery

        // Since tracker relationships don't actually exist, we're gonna have to add a direct join
        $q->joinRaw(
            sprintf(
                " LEFT JOIN tracker ON tracker.item_id=%s.id AND tracker.module_name='%s' AND tracker.user_id='%s' ",
                $q->from->getTableName(),
                $q->from->module_name,
                $GLOBALS['current_user']->id
            ),
            array('alias' => 'tracker')
        );

        // we need to set the linkName to hack around tracker not having real relationships
        /* TODO think about how to fix this so we can be less restrictive to raw joins that don't have a relationship */
        $q->join['tracker']->linkName = 'tracker';

        $td = new SugarDateTime();
        $td->modify($interval);
        $where->queryAnd()->gte("tracker.date_modified", $td->asDb());

        // Now, if they want tracker records, so let's order it by the tracker date_modified
        // clear order by
        $q->order_by = array();
        $q->orderBy('tracker.date_modified', 'DESC');
        // need this to eliminate dupe id's in case you visit the same record many-a-time
        $q->groupBy('id');
        $q->distinct(false);
    }
}
