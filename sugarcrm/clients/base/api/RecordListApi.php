<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once('include/api/SugarApi.php');
require_once('include/RecordListFactory.php');

/*
 * Record List API implementation
 */
class RecordListApi extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            'recordListCreate' => array(
                'reqType' => 'POST',
                'path' => array('<module>','record_list'),
                'pathVars' => array('module',''),
                'jsonParams' => array('filter'),
                'method' => 'recordListCreate',
                'shortHelp' => 'An API to create and save lists of records',
                'longHelp' => 'include/api/help/module_recordlist_post.html',
            ),
            'recordListDelete' => array(
                'reqType' => 'DELETE',
                'path' => array('<module>','record_list','?'),
                'pathVars' => array('module','','record_list_id'),
                'method' => 'recordListDelete',
                'shortHelp' => 'An API to delete an old record list',
                'longHelp' => 'include/api/help/module_recordlist_delete.html',
            ),
            'recordListGet' => array(
                'reqType' => 'GET',
                'path' => array('<module>','record_list','?'),
                'pathVars' => array('module','','record_list_id'),
                'method' => 'recordListGet',
                'shortHelp' => 'An API to fetch a previously created record list',
                'longHelp' => 'include/api/help/module_recordlist_get.html',
            ),
        );
    }

    /**
     * To create a record list
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API containing the module and the records
     * @return array id, module, records
     */
    public function recordListCreate($api, $args)
    {
        $seed = BeanFactory::newBean($args['module']);

        if (!$seed->ACLAccess('access')) {
            throw new SugarApiExceptionNotAuthorized();
        }

        if (!is_array($args['records'])) {
            throw new SugarApiExceptionMissingParameter();
        }
        
        $id = RecordListFactory::saveRecordList($args['records'], $args['module']);

        $loadedRecordList = RecordListFactory::getRecordList($id);
        
        return $loadedRecordList;
    }

    /**
     * To delete a record list
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API containing the module
     * @return bool Did the delete succeed
     */
    public function recordListDelete($api, $args)
    {
        $seed = BeanFactory::newBean($args['module']);
        if (!$seed->ACLAccess('access')) {
            throw new SugarApiExceptionNotAuthorized();
        }

        if (empty($args['record_list_id'])) {
            throw new SugarApiExceptionMissingParameter();
        }
        if (!$api->user->isAdmin()) {
            $recordList = RecordListFactory::getRecordList($args['record_list_id']);
            if ($recordList['assigned_user_id'] != $api->user->id) {
                throw new SugarApiExceptionNotAuthorized();
            }
        }

        $ret = RecordListFactory::deleteRecordList($args['record_list_id']);

        return true;
    }

    /**
     * To retrieve a record list
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API containing the module and id of the record list
     * @return array The record list
     */
    public function recordListGet($api, $args)
    {
        $seed = BeanFactory::newBean($args['module']);
        if (!$seed->ACLAccess('access')) {
            throw new SugarApiExceptionNotAuthorized();
        }

        $recordList = RecordListFactory::getRecordList($args['record_list_id']);
        if ($recordList == null) {
            throw new SugarApiExceptionNotFound();
        }
        if ($recordList['module_name'] != $args['module']) {
            throw new SugarApiExceptionNotAuthorized();
        }
        if (!$api->user->isAdmin()) {
            if ($recordList['assigned_user_id'] != $api->user->id) {
                throw new SugarApiExceptionNotAuthorized();
            }
        }

        return $recordList;
    }
}
