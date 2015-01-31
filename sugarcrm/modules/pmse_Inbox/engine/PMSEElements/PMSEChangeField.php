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

class PMSEChangeField extends PMSEScriptTask
{
    protected $beanList;
    protected $currentUser;
    protected $evaluator;

    /**
     *
     * @global type $beanList
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        global $beanList, $current_user;
        $this->beanList = $beanList;
        $this->currentUser = $current_user;
        $this->evaluator = new PMSEEvaluator();
        parent::__construct();
    }

    /**
     *
     * @return type
     * @codeCoverageIgnore
     */
    public function getBeanList()
    {
        return $this->beanList;
    }

    /**
     *
     * @return type
     * @codeCoverageIgnore
     */
    public function getCurrentUser()
    {
        return $this->currentUser;
    }

    /**
     *
     * @param type $beanList
     * @codeCoverageIgnore
     */
    public function setBeanList($beanList)
    {
        $this->beanList = $beanList;
    }

    /**
     *
     * @param type $currentUser
     * @codeCoverageIgnore
     */
    public function setCurrentUser($currentUser)
    {
        $this->currentUser = $currentUser;
    }

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
     * $response['flow_filters'] = array('first_id', 'second_id'); 
     * //This attribute is used to filter the execution of the following elements
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
        $isRelated = false;
        $bpmnElement = $this->retrieveDefinitionData($flowData['bpmn_id']);
        $act_field_module = $bpmnElement['act_field_module'];
        $act_fields = $bpmnElement['act_fields'];
        $fields = json_decode($act_fields);
        $ifields = 0;
        
        //$this->bpmLog('INFO', "[$cas_id][$cas_index] Getting**** ".print_r($bpmnElement,true));
        //$related = $bean->get_linked_fields();
        $idMainModule = $bean->id;
        $moduleName = $bean->module_name;

        $this->logger->info("[{$flowData['cas_id']}][{$flowData['cas_index']}] Getting $moduleName ID: $idMainModule");

        //Save original bean of project definition
        $beanModule = $bean;

        if ($act_field_module != $moduleName) {
            $relationships = new DeployedRelationships($bean->module_name);
            $relatedNew = $relationships->get($act_field_module)->getDefinition();
            //$relatedNew = $this->beanHandler->getRelationshipData($act_field_module, $bean->db);
            $left = $relatedNew['lhs_module'];
            $act_field_module = $related = $relatedNew['rhs_module'];

            if (!isset($this->beanList[$act_field_module])) {
                $this->logger->warning(
                    "[{$flowData['cas_id']}][{$flowData['cas_index']}] "
                    . "$act_field_module module is not related to $moduleName, "
                    . "ain't appear in the bean list"
                );
            } else {
                $this->logger->debug(
                    "[{$flowData['cas_id']}][{$flowData['cas_index']}] "
                    . "$moduleName got a related module named: [$act_field_module]"
                );
                // Get Relationship Bean
                $beanRelations = $this->caseFlowHandler->retrieveBean("Relationships");
                $relation = $beanRelations->retrieve_by_sides($left, $related, $bean->db);
                if ($relatedNew['is_custom']) {
                    global $db;
                    $join_key_a = strtolower($relatedNew['join_key_lhs']);
                    $query = "select * from {$relatedNew['join_table']} "
                        . "where $join_key_a = '" . $bean->id
                        . "' AND deleted=0 ORDER BY date_modified DESC";
                    $result = $db->Query($query);
                    $row = $db->fetchByAssoc($result);
                    $moduleBean = BeanFactory::getBean($relatedNew['rhs_module'], $row[$relatedNew['join_key_rhs']]);
                    $list_bean_related = array($moduleBean);
                } else {
                    $ID_Related = $relation['rhs_key'];
                    $beanRelated = $this->caseFlowHandler->retrieveBean("$related");
                    $singleCondition = $ID_Related . "='" . $idMainModule . "'";
                    $list_bean_related = $beanRelated->get_full_list('', $singleCondition);
                }
                $this->logger->warning(
                    "[{$flowData['cas_id']}][{$flowData['cas_index']}] "
                    . "$related $ID_Related field found."
                );

                // Get Related Bean by

                $len = sizeof($list_bean_related);
                if (isset($list_bean_related[$len - 1])) {
                    $this->logger->info(
                        "[{$flowData['cas_id']}][{$flowData['cas_index']}] "
                        . "Getting the last related record of $len records."
                    );
                    $beanRelated = $list_bean_related[$len - 1];
                } else {
                    $beanRelated->retrieve_by_string_fields(array($ID_Related => $idMainModule), true);
                }
                if (!isset($beanRelated->id)) {
                    $this->logger->info(
                        "[{$flowData['cas_id']}][{$flowData['cas_index']}] "
                        . "There is not a data relationship beetween "
                        . "$act_field_module and {$flowData['cas_sugar_module']}"
                    );
                    unset($bean);
                } else {
                    $isRelated = true;
                    $bean = $beanRelated;
                    $this->logger->info(
                        "[{$flowData['cas_id']}][{$flowData['cas_index']}] Related "
                        . "$act_field_module loaded using id: $beanRelated->id"
                    );
                }
            }
        }

        if (isset($bean) && is_object($bean)) {
            $historyData = $this->retrieveHistoryData($moduleName);
            if ($act_field_module == $moduleName || $isRelated) {
                foreach ($fields as $field) {
                    if (isset($bean->field_name_map[$field->field])) {
                        if (!$this->emailHandler->doesPrimaryEmailExists($field, $bean, $historyData)) {
                            $historyData->savePredata($field->field, $bean->{$field->field});
                            $newValue = '';
                            if (is_array($field->value)) {
                                $newValue = $this->evaluator->evaluateExpression(
                                    json_encode($field->value),
                                    $beanModule,
                                    array(),
                                    false
                                );                                
                                $newValue = $this->postProcessValue($newValue, $bean->field_name_map[$field->field]['type']);
                            } else {
                                $newValue = $this->beanHandler->mergeBeanInTemplate($beanModule, $field->value);
                            }
                            $bean->{$field->field} = $newValue; //$field->value;
                        }
                        $historyData->savePostdata($field->field, $field->value);
                        $ifields++;
                    } else {
                        
                        //$this->logger->warning("[{$flowData['cas_id']}][{$flowData['cas_index']}] $moduleClassName->" . $field->field . " not defined");
                    }
                }
                $bean->skipPartialUpdate = true;
                $bean->new_with_id = false;
                $res = $bean->save();
                $scriptTaskExecuted = true;
                $params = array();
                $params['cas_id'] = $flowData['cas_id'];
                $params['cas_index'] = $flowData['cas_index'];
                $params['act_id'] = $bpmnElement['id'];
                $params['pro_id'] = $bpmnElement['pro_id'];
                $params['user_id'] = $this->currentUser->id;
                $params['frm_action'] = 'Event Changed Fields';
                $params['frm_comment'] = 'Changed Fields Applied';
                $params['log_data'] = $historyData->getLog();
                $this->caseFlowHandler->saveFormAction($params);
            } else {
                $this->logger->warning(
                    "[{$flowData['cas_id']}][{$flowData['cas_index']}] "
                    . "Trying to use '$act_field_module' fields to be set in $moduleName"
                );
            }
            $this->logger->info(
                "[{$flowData['cas_id']}][{$flowData['cas_index']}] "
                . "number of fields changed: {$ifields}"
            );
        } else {
            $this->logger->info(
                "[{$flowData['cas_id']}][{$flowData['cas_index']}] "
                . "Fields cannot be changed, none Module was set."
            );
        }
        return $this->prepareResponse($flowData, 'ROUTE', 'CREATE');
    }
    
    public function postProcessValue($value, $fieldType)
    {
        switch (strtolower($fieldType)) {
            case 'date':
                $value = new DateTime($value);
                $value = $value->format("Y-m-d");
                break;
            case 'datetime':
            case 'datetimecombo':
                $value = new DateTime($value);
                $value = $value->format("Y-m-d H:i:s");
                break;
            case 'float':
            case 'double':
            case 'integer':
                $value = (double)$value;
                break;
            case 'string':
                $value = (string)$value;
                break;
            case 'boolean':
                $value = (boolean)$value;
                break;
        }
        return $value;
    }
}
