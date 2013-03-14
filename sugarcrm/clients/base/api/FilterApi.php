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
require_once('include/api/SugarApi.php');
require_once('include/SugarQuery/SugarQuery.php');
require_once('data/Relationships/RelationshipFactory.php');

class FilterApi extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            'filterModuleGet' => array(
                'reqType' => 'GET',
                'path' => array('<module>','filter'),
                'pathVars' => array('module',''),
                'method' => 'filterList',
                'jsonParams' => array('filter'),
                'shortHelp' => 'Lists filtered records.',
                'longHelp' => 'include/api/help/module_filter_get_help.html',
            ),
            'filterModulePost' => array(
                'reqType' => 'POST',
                'path' => array('<module>','filter'),
                'pathVars' => array('module',''),
                'method' => 'filterList',
                'shortHelp' => 'Lists filtered records.',
                'longHelp' => 'include/api/help/module_filter_post_help.html',
            ),
            'filterModuleById' => array(
                'reqType' => 'GET',
                'path' => array('<module>','filter', '?'),
                'pathVars' => array('module','', 'record'),
                'method' => 'filterById',
                'shortHelp' => 'Filter records for a module by a predefined filter id.',
                'longHelp' => 'include/api/help/module_filter_record_get_help.html',
            ),
            'filterRelatedRecords' => array(
                'reqType' => 'GET',
                'path' => array('<module>', '?', 'link', '?', 'filter'),
                'pathVars' => array('module', 'record', '', 'link_name', ''),
                'method' => 'filterRelated',
                'shortHelp' => 'Lists related filtered records.',
                'longHelp' => 'include/api/help/module_record_link_link_name_filter_get_help.html',

            ),
        );
    }

    protected $defaultLimit = 20; // How many records should we show if they don't pass up a limit

    protected $current_user;

    public function __construct()
    {
        global $current_user;
        $this->current_user = $current_user;
    }

    public function filterById(ServiceBase $api, array $args)
    {
        $filter = BeanFactory::getBean('Filters', $args['record']);
        $filter_definition = json_decode($filter->filter_definition, true);
        $args = array_merge($args, $filter_definition);
        unset($args['record']);
        return $this->filterList($api, $args);
    }

    protected function parseOptions(ServiceBase $api, array $args, SugarBean $seed)
    {
        $options = array();

        // Set up the defaults
        $options['limit'] = $this->defaultLimit;
        $options['offset'] = 0;
        $options['order_by'] = array(array('date_modified','DESC'));

        if (!empty($args['max_num'])) {
            $options['limit'] = (int)$args['max_num'];
        }
        if (!empty($args['offset'])) {
            if ($args['offset'] == 'end') {
                $options['offset'] = 'end';
            } else {
                $options['offset'] = (int)$args['offset'];
            }
        }
        if (!empty($args['order_by'])) {
            $orderBys = explode(',', $args['order_by']);
            $orderByArray = array();
            foreach ($orderBys as $order) {
                $orderSplit = explode(':', $order);

                if (!$seed->ACLFieldAccess($orderSplit[0], 'list') || !isset($seed->field_defs[$orderSplit[0]])) {
                    throw new SugarApiExceptionNotAuthorized('No access to view field: '.$orderSplit[0].' in module: '.$args['module']);
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
        $options['module'] = $seed->module_name;

        //Set the list of fields to be used in the select.
        $options['select'] = !empty($args['fields']) ? explode(",", $args['fields']) : array();
        //Force id and date_modified into the select
        $options['select'][] = "id";
        $options['select'][] = "date_modified";
        $options['select'] = array_unique($options['select']);

        return $options;
    }

    public function filterList(ServiceBase $api, array $args)
    {
        $seed = BeanFactory::newBean($args['module']);

        if (! $seed->ACLAccess('list')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: '.$args['module']);
        }

        $options = $this->parseOptions($api, $args, $seed);

        $q = $this->getQueryObject($seed, $options);

        // return $args['filter'];
        if (!isset($args['filter']) || !is_array($args['filter'])) {
            $args['filter'] = array();
        }
        $this->addFilters($args['filter'], $q->where(), $q);

        return $this->runQuery($api, $args, $q, $options, $seed);
    }

    public function filterRelated(ServiceBase $api, array $args)
    {
        // Load the parent bean.
        $record = BeanFactory::retrieveBean($args['module'], $args['record']);

        if (empty($record)) {
            throw new SugarApiExceptionNotFound('Could not find parent record '.$args['record'].' in module '.$args['module']);
        }
        if (!$record->ACLAccess('view')) {
            throw new SugarApiExceptionNotAuthorized('No access to view records for module: '.$args['module']);
        }

        // Load the relationship.
        $linkName = $args['link_name'];
        if (!$record->load_relationship($linkName)) {
            // The relationship did not load.
            throw new SugarApiExceptionNotFound('Could not find a relationship named: '.$args['link_name']);
        }
        $linkModuleName = $record->$linkName->getRelatedModuleName();
        $linkSeed = BeanFactory::getBean($linkModuleName);
        if (!$linkSeed->ACLAccess('list')) {
            throw new SugarApiExceptionNotAuthorized('No access to list records for module: '.$linkModuleName);
        }

        $rf = SugarRelationshipFactory::getInstance();
        $relObj = $record->$linkName->getRelationshipObject();
        $relDef = $rf->getRelationshipDef($relObj->name);
        $tableName = $linkName;
        foreach ($linkSeed->field_defs as $def) {
            if ($def['type'] !== 'link') {
                continue;
            }
            if ($def['relationship'] === $relObj->name) {
                $tableName = $def['name'];
                break;
            }
        }

        if ($record->$linkName->getSide() == REL_LHS) {
            $column = $relDef['rhs_key'];
        } else {
            $column = $relDef['lhs_key'];
        }

        $options = $this->parseOptions($api, $args, $linkSeed);
        $q = $this->getQueryObject($linkSeed, $options);
        if (!isset($args['filter']) || !is_array($args['filter'])) {
            $args['filter'] = array();
        }
        $args['filter'][][$tableName . '.' . $column] = array('$equals' => $record->id);
        $this->addFilters($args['filter'], $q->where(), $q);
        return $this->runQuery($api, $args, $q, $options, $linkSeed);
    }

    protected function getQueryObject(SugarBean $seed, array $options)
    {
        $q = new SugarQuery();
        // Just need ID, we need to fetch beans so we can format them later.
        $q->from($seed);
        if(empty($options['select'])) {
            $options['select'] = array('id', 'date_modified');
        }

        $fields = array();
        foreach($options['select'] as $field) {
            // FIXME: convert this to vardefs too?
            //BEGIN SUGARCRM flav=pro ONLY
            if($field == 'my_favorite') {
                $fjoin = $q->join("favorites");
                $fields[] = array($fjoin->joinName().".id", 'my_favorite');
                continue;
            }
            //END SUGARCRM flav=pro ONLY

            // fields that aren't in field defs are removed, since we don't know what to do with them
            if(!empty($seed->field_defs[$field])) {
                $fields[] = $field;
            }
        }
        $q->select($fields);
        $q->distinct(true);
        $q->where()->equals("deleted", 0);

        foreach ($options['order_by'] as $orderBy) {
            $q->orderBy($orderBy[0], $orderBy[1]);
        }
        // Add an extra record to the limit so we can detect if there are more records to be found
        $q->limit($options['limit']+1);
        $q->offset($options['offset']);

        return $q;
    }


    /**
     * Populate related beans from data array
     * @param SugarBean $bean
     * @param array $data
     */
    protected function populateRelatedFields($bean, $data)
    {
        $relates = array();
        // fill in related rows data by field
        foreach($data as $key => $value) {
            if(($split = strpos($key, "__")) > 0) {
                $relates[substr($key, 0, $split)][] = substr($key, $split+2);
            }
        }

        foreach($bean->field_defs as $field => $fieldDef) {
            if($fieldDef['type'] == 'relate' && !empty($fieldDef['link'])) {
                if(empty($data[$field]) && empty($relates[$field])) continue;

                $rbean = $bean->getRelatedBean($fieldDef['link']);
                if(empty($rbean)) continue;

                if(!empty($data[$field])) {
                    // we have direct data - populate it
                    $rbean->populateFromRow(array($fieldDef['rname'] => $data[$field]), true);
                } else {
                    if(empty($relates[$field])) {
                        continue;
                    }

                    $reldata = array();
                    foreach($relates[$field] as $relfield) {
                        $reldata[$relfield] = $data["{$field}__{$relfield}"];
                    }
                    if(!empty($reldata)) {
                        $rbean->populateFromRow($reldata, true);
                    }
                }

                if(empty($rbean->id) && !empty($fieldDef['id_name'])) {
                	$rbean->id = $data[$fieldDef['id_name']];
                }

            }
        }
        // Call some data fillings for the bean
        foreach($bean->related_beans as $rbean) {
            if(empty($rbean->id)) continue;

            $rbean->check_date_relationships_load();
            // $rbean->fill_in_additional_list_fields();
            if($rbean->hasCustomFields()) $rbean->custom_fields->fill_relationships();
            $rbean->call_custom_logic("process_record");
        }
    }

    protected function runQuery(ServiceBase $api, array $args, SugarQuery $q, array $options, SugarBean $seed)
    {
        $GLOBALS['log']->info("Filter SQL: ".$q->compileSql());
        $idRows = $q->execute();
        // return $idRows;

        $data = array();
        $data['next_offset'] = -1;

        $beans = $bean_ids = array();
        foreach ($idRows as $i => $row) {
            if ($i == $options['limit']) {
                $data['next_offset'] = (int)($options['limit']+$options['offset']);
                continue;
            }
            if (empty($args['fields'])){
                //FIXME: Without a field list, we need to just do a full retrieve to make sure we get the entire bean.
                $bean = BeanFactory::getBean($options['module'], $row['id']);
            } else {
                $bean = clone $seed;
                // convert will happen inside populateFromRow
                $bean->loadFromRow($row, true);
                $this->populateRelatedFields($bean, $row);
            }
            if ($bean && !empty($bean->id)) {
                $beans[$bean->id] = $bean;
                $bean_ids[] = $bean->id;
            }
        }
        /* FIXME: this is a hack for emails, think about how to fix it */
        if (isset($seed->field_defs['email']) && in_array('email',$options['select'])) {
            $email = BeanFactory::getBean('EmailAddresses');
            $q = $email->getEmailsQuery($seed->module_name);
            $q->where()->in("ear.bean_id", $bean_ids);
            $q->select->field("ear.bean_id");
            $email_rows = $q->execute();
            foreach($email_rows as $email) {
                $id = $email['bean_id'];
                unset($email['bean_id']);
                $beans[$id]->emailData[] = $email;
            }
        }

        $data['records'] = $this->formatBeans($api, $args, $beans);

        return $data;
    }

    protected function addFilters(array $filterDefs, SugarQuery_Builder_Where $where, SugarQuery $q)
    {
        foreach ($filterDefs as $filterDef) {
            foreach ($filterDef as $field => $filter) {
                if ($field == '$or') {
                    $this->addFilters($filter, $where->queryOr(), $q);
                } else if ($field == '$and') {
                    $this->addFilters($filter, $where->queryAnd(), $q);
                } else if ($field == '$favorite') {
                    $this->addFavoriteFilter($q, $where, $filter);
                } else if ($field == '$owner') {
                    $this->addOwnerFilter($q, $where, $filter);
                } else if ($field == '$creator') {
                    $this->addCreatorFilter($q, $where, $filter);
                } else if ($field == '$tracker') {
                    $this->addTrackerFilter($q, $where, $filter);
                } else {
                    // Looks like just a normal field, parse it's options
                    if ( strpos($field, '.')) {
                        // It looks like it's a related field that it's searching by
                        list($relatedTable, $relatedField) = explode('.', $field);
                        $q->join($relatedTable, array('joinType'=>'LEFT'));
                    }

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
                                $where->in($field, $value);
                                break;
                            case '$not_in':
                                $where->notIn($field, $value);
                                break;
                            case '$between':
                                $where->between($field, $value);
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
                            case '$fromDays':
                                // FIXME: FRM-226, logic for these needs to be moved to SugarQuery
                                $where->addRaw("{$field} >= DATE_ADD(NOW(), INTERVAL {$value} DAY)");
                                break;
                            default:
                                throw new SugarApiExceptionInvalidParameter("Did not recognize the operand: ".$op);
                        }
                    }
                }

            }
        }
    }

    /**
     * This function adds an owner filter to the sugar query
     * @param SugarQuery $q The whole SugarQuery object
     * @param SugarQuery_Builder_Where $where The Where part of the SugarQuery object
     * @param string $link Which module are you adding the owner filter to.
     */
    protected function addOwnerFilter(SugarQuery $q, SugarQuery_Builder_Where $where, $link)
    {
        if ($link == '' || $link == '_this') {
            $linkPart = '';
        } else {
            $q->join($link, array('joinType'=>'LEFT'));
            $linkPart = $link.'.';
        }

        $where->equals($linkPart.'assigned_user_id', $this->current_user->id);
    }

    /**
     * This function adds a creator filter to the sugar query
     * @param SugarQuery $q The whole SugarQuery object
     * @param SugarQuery_Builder_Where $where The Where part of the SugarQuery object
     * @param string $link Which module are you adding the owner filter to.
     */
    protected function addCreatorFilter(SugarQuery $q, SugarQuery_Builder_Where $where, $link)
    {
        if ($link == '' || $link == '_this') {
            $linkPart = '';
        } else {
            $q->join($link, array('joinType'=>'LEFT'));
            $linkPart = $link.'.';
        }

        $where->equals($linkPart.'created_by', $this->current_user->id);
    }

    /**
     * This function adds a favorite filter to the sugar query
     * @param SugarQuery $q The whole SugarQuery object
     * @param SugarQuery_Builder_Where $where The Where part of the SugarQuery object
     * @param string $link Which module are you adding the favorite filter to.
     */
    protected function addFavoriteFilter(SugarQuery $q, SugarQuery_Builder_Where $where, $link)
    {
        $sfOptions = array('joinType'=>'LEFT');
        if ($link == '' || $link == '_this') {
        } else {
            $q->join($link, array('joinType'=>'LEFT'));
            $sfOptions['joinTo'] = $link;
        }
        $fjoin = $q->join("favorites");

        $where->notNull($fjoin->joinName() . '.id');
    }

    protected function addTrackerFilter(SugarQuery $q, SugarQuery_Builder_Where $where, $interval)
    {
        // FIXME: FRM-226, logic for these needs to be moved to SugarQuery

        // Since tracker relationships don't actually exist, we're gonna have to add a direct join
        $q->joinRaw(" LEFT JOIN tracker ON tracker.item_id={$q->from->getTableName()}.id "
                    ."AND tracker.module_name='{$q->from->module_name}' "
                    ."AND tracker.user_id='{$GLOBALS['current_user']->id}' ",array('alias'=>'tracker'));

        $td = new SugarDateTime();
        $td->modify($interval);
        $where->addRaw("tracker.date_modified >= '".$td->asDb()."' ");

        // Now, if they want tracker records, so let's order it by the tracker date_modified
        $q->order_by = array(array('tracker.date_modified','DESC'));

        // Also, turn the distinct part off otherwise the sorting doesn't work.
        $q->distinct(false);
    }
}
