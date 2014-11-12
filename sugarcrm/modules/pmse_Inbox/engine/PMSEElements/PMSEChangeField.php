<?php

require_once 'PMSEScriptTask.php';

class PMSEChangeField extends PMSEScriptTask
{
    protected $beanList;
    protected $currentUser;

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
            $relatedNew = $this->beanHandler->getRelationshipData($act_field_module, $bean->db);
            $left = $relatedNew['lhs_module'];
            $act_field_module = $related = $relatedNew['rhs_module'];

            if (!isset($this->beanList[$act_field_module])) {
                $this->logger->warning("[{$flowData['cas_id']}][{$flowData['cas_index']}] $act_field_module module is not related to $moduleName, ain't appear in the bean list");
            } else {
                $this->logger->debug("[{$flowData['cas_id']}][{$flowData['cas_index']}] $moduleName got a related module named: [$act_field_module]");
                // Get Relationship Bean
                $beanRelations = $this->caseFlowHandler->retrieveBean("Relationships");
                $relation = $beanRelations->retrieve_by_sides($left, $related, $bean->db);
                $ID_Related = $relation['rhs_key'];
                $this->logger->warning("[{$flowData['cas_id']}][{$flowData['cas_index']}] $related $ID_Related field found.");

                // Get Related Bean by
                $beanRelated = $this->caseFlowHandler->retrieveBean("$related");

                $singleCondition = $ID_Related . "='" . $idMainModule . "'";
                $list_bean_related = $beanRelated->get_full_list('', $singleCondition);
                $len = sizeof($list_bean_related);
                if (isset($list_bean_related[$len - 1])) {
                    $this->logger->debug("[{$flowData['cas_id']}][{$flowData['cas_index']}] Getting the last related record of $len records.");
                    $beanRelated = $list_bean_related[$len - 1];
                } else {
                    $beanRelated->retrieve_by_string_fields(array($ID_Related => $idMainModule), true);
                }
                if (!isset($beanRelated->id)) {
                    $this->logger->debug("[{$flowData['cas_id']}][{$flowData['cas_index']}] There is not a data relationship beetween $act_field_module and {$flowData['cas_sugar_module']}");
                    unset($bean);
                } else {
                    $bean = $this->caseFlowHandler->retrieveBean("{$related}", $beanRelated->id);
                    $this->logger->debug("[{$flowData['cas_id']}][{$flowData['cas_index']}] Related $act_field_module loaded using id: $beanRelated->id");
                }
            }
        }

        if (isset($bean) && is_object($bean)) {
            $historyData = $this->retrieveHistoryData($moduleName);
            if ($act_field_module == $moduleName || $act_field_module == '') {
                foreach ($fields as $field) {
                    if (isset($bean->field_name_map[$field->field])) {
                        if (!$this->emailHandler->doesPrimaryEmailExists($field, $bean, $historyData)) {
                            $historyData->savePredata($field->field, $bean->{$field->field});
                            $newValue = '';
                            if ($field->type == 'Date' || $field->type == 'Datetime') {
                                list($date_t, $newValue) = $this->beanHandler->calculateDueDate($field->value, $beanModule);
                            } elseif ($field->type == 'Integer' || $field->type == 'Float' ||
                                    $field->type == 'Decimal' || $field->type == 'Currency') {
                                $newValue = $this->beanHandler->processValueExpression($field->value, $beanModule);
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
                $res = $bean->save(true);
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
                $this->logger->warning("[{$flowData['cas_id']}][{$flowData['cas_index']}] Trying to use '$act_field_module' fields to be set in $moduleName");
            }
            $this->logger->debug("[{$flowData['cas_id']}][{$flowData['cas_index']}] number of fields changed: {$ifields}");
        } else {
            $this->logger->debug("[{$flowData['cas_id']}][{$flowData['cas_index']}] Fields cannot be changed, none Module was set.");
        }
        return $this->prepareResponse($flowData, 'ROUTE', 'CREATE');
    }

}
