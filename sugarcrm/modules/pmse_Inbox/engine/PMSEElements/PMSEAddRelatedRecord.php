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

require_once 'PMSEScriptTask.php';

class PMSEAddRelatedRecord extends PMSEScriptTask
{
    /**
     * This method prepares the response of the current element based on the
     * $bean object and the $flowData, an external action such as
     * ROUTE or ADHOC_REASSIGN could be also processed.
     *
     * This method probably should be override for each new element, but it's
     * not mandatory. However the response structure always must pass using
     * the 'prepareResponse' Method.
     *
     * As defined in the example:
     *
     * $response['route_action'] = 'ROUTE'; //The action that should process the Router
     * $response['flow_action'] = 'CREATE'; //The record action that should process the router
     * $response['flow_data'] = $flowData; //The current flowData
     * $response['flow_filters'] = array('first_id', 'second_id'); //This attribute is used to filter the execution of the following elements
     * $response['flow_id'] = $flowData['id']; // The flowData id if present
     *
     *
     * @param type $flowData
     * @param type $bean
     * @param type $externalAction
     * @return type
     */
    public function run($flowData, $bean = null, $externalAction = '', $arguments = array())
    {
        $bpmnElement = $this->retrieveDefinitionData($flowData['bpmn_id']);
        $definitionBean = $this->caseFlowHandler->retrieveBean('pmse_BpmActivityDefinition'); //new BpmActivityDefinition();
        $processDefinitionBean = $this->caseFlowHandler->retrieveBean('pmse_BpmProcessDefinition'); //new BpmProcessDefinition();
        $definitionBean->retrieve_by_string_fields(array('id' => $bpmnElement['id']));
        $processDefinitionBean->retrieve_by_string_fields(array('id' => $definitionBean->pro_id));

        if ((isset($definitionBean->act_field_module) && !empty($definitionBean->act_field_module)) &&
            (isset($definitionBean->act_fields) && !empty($definitionBean->act_fields))
        ) {

            $arr_module = $definitionBean->act_field_module;
            $arr_fields = json_decode($definitionBean->act_fields);

            // TODO: Probably the act_module field should be used instead of pro_module
            $sugarModule = $processDefinitionBean->pro_module;

            //Get module from RelationShips
            $relationships = $this->beanHandler->getDeployedRelationships($sugarModule);
            $rel_module = $relationships->get($arr_module)->getDefinition();

            //$this->bpmLog('INFO', "Arr_module: $arr_module, Sugar_module: $sugarModule, RelModule: " .  $rel_module['rhs_module'] . " NOTE id: " . $beanFactory->id .
            //      " ModuleName: $moduleName, ObjectId: $object_id");
            //Add Relationship
            if ($rel_module['is_custom']) {
                $rel_name = $rel_module['relationship_name'];
            } else {
                $rel_name = strtolower($rel_module['rhs_module']);
            }

            if ($bean->load_relationship($rel_name)) {
                $relatedModule = $this->caseFlowHandler->retrieveBean($rel_module['rhs_module']);
                if (count($arr_fields) > 0) {
                    foreach ($arr_fields as $value) {
                        if (!empty($value->field) && !empty($value->value)) {
                            $key = $value->field;
                            $newValue = '';
                            if ($value->type == 'Datetime') {
                                list($date_t, $newValue) = $this->beanHandler->calculateDueDate($value->value, $bean);
                            } elseif ($key == 'assigned_user_id') {
                                switch ($value->value) {
                                    case 'currentuser':
                                        $newValue = $this->beanHandler->mergeBeanInTemplate($bean,
                                            $this->userAssignmentHandler->getCurrentUserId());
                                        break;
                                    case 'supervisor':
                                        $newValue = $this->beanHandler->mergeBeanInTemplate($bean,
                                            $this->userAssignmentHandler->getSupervisorId($this->getCurrentUserId()));
                                        break;
                                    case 'owner':
                                        $newValue = $this->beanHandler->mergeBeanInTemplate($bean,
                                            $this->userAssignmentHandler->getRecordOwnerId($bean->id, $sugarModule));
                                        break;
                                    default:
                                        $newValue = $this->beanHandler->mergeBeanInTemplate($bean, $value->value);
                                        break;
                                }
                            } elseif ($value->type == 'Integer' || $value->type == 'Float' ||
                                $value->type == 'Decimal' || $value->type == 'Currency'
                            ) {
                                $newValue = $this->beanHandler->processValueExpression($value->value, $bean);
                            } else {
                                $newValue = $this->beanHandler->mergeBeanInTemplate($bean, $value->value);
                            }
                            $relatedModule->$key = $newValue;
                            $this->logger->info("Data generated $newValue for $key");
                        }
                    }
                    $relatedModule->save();
                    if (!$relatedModule->in_save) {
                        $rel_id = $relatedModule->id;
                        $this->logger->debug("Create related record " . $rel_module['rhs_module'] . " ID: $rel_id");
                        $bean->$rel_name->add($rel_id); //Note use of $rel_name
                        $this->logger->debug("Add relationship $rel_name of $sugarModule");

                        $scriptTaskExecuted = true;
                    } else {
                        $this->logger->info("Not created related record!!!");
                    }
                }
            } else {
                $this->logger->info("Not load relationship $rel_name of $sugarModule");
            }
        } else {
            $this->logger->info("Not configure related record script task");
            $scriptTaskExecuted = true;
        }
        $this->logger->debug("Script executed");
        return $this->prepareResponse($flowData, 'ROUTE', 'CREATE');
    }

}
