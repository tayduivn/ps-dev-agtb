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

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('PMSEHandlers/PMSEHookHandler.php');
//require_once('PMSEEngine.php');

include('PMSEModules.php');

class PMSELogicHook
{
    function before_save($bean, $event, $arguments)
    {
        if ($this->verifyModules($bean)) {
            return true;
        }

        //Declare Engine Class
        $hookHandler = new PMSEHookHandler();

        //if this is a new record or and updated record
        $isNewRecord = empty($bean->fetched_row['id']);

        return $hookHandler->runStartEventBeforeSave($bean, $event, $arguments, array(), $isNewRecord);
    }

    function after_save($bean, $event, $arguments)
    {
        if ($this->verifyModules($bean)) {
            return true;
        }
        $handler = new PMSEHookHandler();
        return $handler->runStartEventAfterSave($bean, $event, $arguments);
    }

    function after_delete($bean, $event, $arguments)
    {
        if ($this->verifyModules($bean)) {
            return true;
        }

        $handler = new PMSEHookHandler();
        return $handler->terminateCaseAfterDelete($bean, $event, $arguments);
    }

    /**
     *
     * @param type $bean
     * @param type $event
     * @param type $arguments
     * @return boolean
     * @deprecated since version pmse2
     * @codeCoverageIgnore
     */
    function after_retrieve($bean, $event, $arguments)
    {
        return true;
    }

    private function verifyModules($bean)
    {
        include('PMSEModules.php');
        $pmseModulesList = (isset($pmseModulesList)) ? $pmseModulesList : array();
        //returns immediately if the bean is a common module
        $result = false;
        if (isset($bean->module_name)) {
            $commonModules = array_merge(array(
                    'Teams',
                    'Users',
                    'UserPreferences',
                    'Subscriptions',
                    'OAuthToken',
                    'Dashboards',
                    'Activities'
                ), $pmseModulesList);
            //if ($bean->object_name == 'OAuthToken') {
            //    return true;
            //}
            if (in_array($bean->module_name, $commonModules) OR in_array($bean->object_name, $commonModules)) {
                return true;
            }
        }

        //if module is pmse_Inbox and we are routing a case, then returns immediately
        if (    
                (isset($_REQUEST['Type']) && !empty($_REQUEST['Type']))
                || 
                (isset($_REQUEST['frm_action']) && !empty($_REQUEST['frm_action']))
            ) {
            return true;
        }

        //if the record is going to be updated by the engine, we need to skip the "partial update" section
        if (isset($bean->skipPartialUpdate) && $bean->skipPartialUpdate) {
            return true;
        }
        return $result;
    }

    /**
     *
     * @param type $bean_name
     * @return type
     * @deprecated since version pmse2
     * @codeCoverageIgnore
     */
    private function getStartEvents($bean_name = '')
    {
        //$bean = BeanFactory::newBean('pmse_BpmnEvent');
        //$rows = $bean->get_full_list("pmse_bpmn_event.id", "evn_type = 'START'");
        //return $rows;
        $where = (!empty($bean_name)) ? " AND b.evn_module = '{$bean_name}'" : "";
        $fields = array(
            'id',
            'evn_uid',
            'prj_id',
            'pro_id',
            'evn_type',
            'evn_marker',
            'evn_is_interrupting',
            'evn_attached_to',
            'evn_cancel_activity',
            'evn_activity_ref',
            'evn_wait_for_completion',
            'evn_error_name',
            'evn_error_code',
            'evn_escalation_name',
            'evn_escalation_code',
            'evn_condition',
            'evn_message',
            'evn_operation_name',
            'evn_operation_implementation',
            'evn_time_date',
            'evn_time_cycle',
            'evn_time_duration',
            'evn_behavior',
        );
        //TODO change method to join tables
        $bean = BeanFactory::newBean('pmse_BpmnEvent');
        $q = new SugarQuery();
        $q->select($fields);
        $q->from($bean, array('alias' => 'a'));
        //$q->joinTable('pmse_bpm_event_definition', array('alias' => 'b', 'joinType' => 'LEFT', 'linkingTable' => true))
        //    ->on()
        //    ->equalsField('b.id', 'a.id')
        //    ->equals('b.evn_status', 'ACTIVE');
        $q->joinRaw("LEFT JOIN pmse_bpm_event_definition b ON (b.id=a.id AND b.evn_status = 'ACTIVE')",
            array('alias' => 'b'));
        $q->joinRaw("INNER JOIN pmse_bpm_process_definition c ON (a.prj_id = c.prj_id AND c.pro_status='ACTIVE')",
            array('alias' => 'c'));
        $q->where()->queryAnd()
            ->addRaw("a.evn_type= 'START' AND b.evn_status = 'ACTIVE' AND c.pro_status='ACTIVE'" . $where);
        $q->select->fieldRaw('b.evn_status, b.evn_type, b.evn_module, b.evn_criteria, b.evn_params, b.evn_script');
        $q->select->fieldRaw('c.pro_module, c.pro_status, c.pro_locked_variables, c.pro_terminate_variables');
        //$v = $q->compileSql();
        $rows = $q->execute();
        return $rows;
    }


    /**
     *
     * @param type $bean_name
     * @return type
     * @deprecated since version pmse2
     * @codeCoverageIgnore
     */
    private function getReceiveMessageEvents($bean_name = '')
    {
        $where = (!empty($bean_name)) ? " AND a.rel_element_module = '{$bean_name}'" : "";
        $fields = array(
            'id',
            'pro_id',
            'rel_element_module',
            'rel_element_id',
        );
        //TODO change method to join tables
        $bean = BeanFactory::newBean('pmse_BpmRelatedDependency');
        $q = new SugarQuery();
        $q->select($fields);
        $q->from($bean, array('alias' => 'a'));
        //$q->joinTable('pmse_bpm_event_definition', array('alias' => 'b', 'joinType' => 'LEFT', 'linkingTable' => true))
        //    ->on()
        //    ->equalsField('b.id', 'a.id')
        //    ->equals('b.evn_status', 'ACTIVE');
        $q->where()->queryAnd()
            ->addRaw("a.deleted=0" . $where);
        //$v = $q->compileSql();
        $rows = $q->execute();
        return $rows;
    }

}
