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

require_once 'PMSEEvent.php';

class PMSEStartEvent extends PMSEEvent
{
    /**
     *
     * @param type $flowData
     * @param type $bean
     * @param type $externalAction
     * @param type $arguments
     * @return type
     */
    public function run($flowData, $bean = null, $externalAction = '', $arguments = array())
    {
//        $evaluation = $this->evaluateExpression($flowData, $bean);
        $relatedBean = $this->retrieveRelatedBean($flowData, $bean);
//        if ($evaluation) {
        if (!empty($relatedBean)) {
            $flowData = $this->createNewCase($relatedBean, $flowData);
        } else {
            $flowData = $this->createNewCase($bean, $flowData);
        }
//        } else {
        //$this->bpmLog('DEBUG', "start: {$bean->id} doesn't satisfied condition in process $pro_id");
//        }
        return parent::run($flowData, $bean, $externalAction, $arguments);
    }

    /**
     *
     * @param type $flowData
     * @param type $bean
     * @return type
     */
    public function retrieveRelatedBean($flowData, $bean)
    {
        $processDefinitionBean = $this->caseFlowHandler->retrieveBean('pmse_BpmProcessDefinition', $flowData['pro_id']);
        $relatedBean = '';
        if ($processDefinitionBean->pro_module != $bean->module_name) {
            foreach ($bean as $key => $attribute) {
                if ($bean->$key instanceof Link2) {
                    if (
                        ($bean->$key->relationship->def['lhs_module'] == $processDefinitionBean->pro_module
                            && $bean->$key->relationship->def['rhs_module'] == $bean->module_name) ||
                        ($bean->$key->relationship->def['lhs_module'] == $bean->module_name
                            && $bean->$key->relationship->def['rhs_module'] == $processDefinitionBean->pro_module)
                    ) {
                        $relatedBeanList = $bean->$key->getBeans();
                        if ($relatedBean = array_pop($relatedBeanList)) {
                            $relatedBean->load_relationships();
                        }
                    }
                }
            }
        }
        return $relatedBean;
    }

    /**
     * Creates a new case based on a determined module and start Event
     * @param type $bean
     * @param type $event
     * @param type $flowData
     * @return type
     * @codeCoverageIgnore
     */
    private function createNewCase($bean, $elementData)
    {
        //set fields
        $moduleName = $bean->module_name;
        $objectId = $bean->id;

        //autoincrement, if cas_title is empty we need to update after the insert
        $cas_id = 0;
        $updateCaseWithNumber = false;

        $today = TimeDate::getInstance()->nowDb();
        $_date = TimeDate::getInstance()->getNow()->add(new DateInterval('P2D'));
        $dueDate = $_date->asDb();

        //todo: generate a pin
        $cas_pin = rand(0, 10000);

        //execute queries to get the correct process Id and process title
        $pro_id = $elementData['pro_id'];
        $processBean = BeanFactory::getBean('pmse_BpmnProcess', $pro_id); //new BpmnProcess();

        if (!$processBean->fetched_row) {
            $this->logger->error("[$cas_id][1] process name not found using Process Number: $pro_id");
            //$this->bpmLog('ERROR', "[$cas_id][1] process name not found using Process Id: $pro_id");
            $pro_title = 'unknown';
        } else {
            $pro_title = $processBean->name;
        }

        if (isset($bean->assigned_user_id)) {
            $assigned_user_id = $bean->assigned_user_id;
        } else {
            if (isset($bean->created_by)) {
                $assigned_user_id = $bean->created_by;
            } else {
                $assigned_user_id = '';
            }
        }

        $trimmedName = isset($bean->name) ? trim($bean->name) : '';
        $trimmedUserName = isset($bean->user_name) ? trim($bean->user_name) : '';
        if (!empty($trimmedName)) {
            $cas_title = $bean->name;
        } elseif (!empty($trimmedUserName)) {
            $cas_title = $bean->user_name;
        } else {
            $cas_title = "Case without name";
            $updateCaseWithNumber = true;
        }

        //TODO this is for work, remove after solutions
        $sql = 'select max(cas_id) as cas_id from pmse_inbox';
        $case_aux = $this->dbHandler->Query($sql);
        $row_aux = $this->dbHandler->fetchByAssoc($case_aux);
        if (is_array($row_aux)) {
            $cas_id_aux = (int)$row_aux['cas_id'] + 1;
        } else {
            $cas_id_aux = 1;
        }
        //create a ProcessMaker row
        $case = BeanFactory::getBean('pmse_Inbox'); //new BpmInbox();
        $case->name = $cas_title;
        $case->cas_id = $cas_id; //0 value for autoincrement
        $case->cas_parent = 0;
        $case->cas_status = 'IN PROGRESS';
        $case->cas_title = $cas_title;
        $case->pro_id = $pro_id;
        $case->pro_title = $pro_title;
        $case->cas_custom_status = '';
        $case->cas_init_user = $assigned_user_id;
        $case->assigned_user_id = $assigned_user_id;
        $case->cas_create_date = $today;
        $case->cas_update_date = $today;
        $case->cas_finish_date = '';
        $case->cas_pin = $cas_pin;

        $saved = false;
        //$case->new_with_id = true;
        $case->save();

        if (!$case->in_save) {
            $saved = true;
            //$cas_id = $case->db->database->insert_id;
            if (empty($case->cas_id)) {
                $cas_id = $cas_id_aux;
                //$this->bpmLog('ERROR', "Error to generated autonumeric case - aux: [$cas_id] - id: [$case->cas_id]");
            } else {
                $cas_id = $case->cas_id;
            }
            if ($updateCaseWithNumber) {
                $case->cas_title = "Process # $cas_id";
                $case->new_with_id = false;
                $case->save();
            }
            //$this->bpmLog('INFO', "[$cas_id][1] new case for {$bean->module_name}:$objectId in process '$pro_title'");
            //todo: throw in case something goes wrong.
        }

        $flowData = array();

        $flowData['cas_id'] = $cas_id;
        $flowData['cas_index'] = 1;
        $flowData['cas_previous'] = 0;
        $flowData['pro_id'] = $pro_id;
        $flowData['bpmn_id'] = $elementData['bpmn_id'];
        $flowData['bpmn_type'] = 'bpmnEvent';
        $flowData['cas_user_id'] = $assigned_user_id;
        $flowData['cas_thread'] = 1;
        $flowData['cas_flow_status'] = 'NEW';
        $flowData['cas_sugar_module'] = $moduleName;
        $flowData['cas_sugar_object_id'] = $objectId;
        $flowData['cas_sugar_action'] = 'None';
        $flowData['cas_delegate_date'] = $today;
        $flowData['cas_start_date'] = $today; //all start events are started inmediately
        $flowData['cas_finish_date'] = '';
        $flowData['cas_due_date'] = $dueDate;
        $flowData['cas_queue_duration'] = 0;
        $flowData['cas_duration'] = 0;
        $flowData['cas_delay_duration'] = 0;
        $flowData['cas_started'] = 1; //all start events are started inmediately
        $flowData['cas_finished'] = 0;
        $flowData['cas_delayed'] = 0;

        // call to the new engine classes
        //$this->newFollowFlow($flowData, true, $bean);
        return $flowData;
    }
}
