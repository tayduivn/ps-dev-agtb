<?php

require_once 'PMSEIntermediateEvent.php';

class PMSETimerEvent extends PMSEIntermediateEvent
{

    /**
     * 
     * @return type
     * @codeCoverageIgnore
     */
    public function getCurrentTime()
    {
        $date = new DateTime();
        $now = $date->getTimestamp();
        return $now;
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
    public function run($flowData, $bean, $externalAction = '', $arguments = array())
    {        
        if (empty($externalAction)) {
            $eventDefinition = $this->retrieveDefinitionData($flowData['bpmn_id']);
            $flowData['cas_flow_status'] = 'SLEEPING';
            $eventCriteria = json_decode($eventDefinition['evn_criteria']);
            if (!is_array($eventCriteria)) {
                $duration = $eventDefinition['evn_criteria'] . ' ' . $eventDefinition['evn_params'];
                $flowData['cas_due_date'] = date('Y-m-d H:i:s', strtotime("+$duration"));
                //$this->bpmLog('INFO', "[$cas_id][$newCasIndex] schedule a timer event for $dueDate");
            } else {
                $moduleName = $flowData['cas_sugar_module'];
                $object_id = $flowData['cas_sugar_object_id'];
                $bean = $this->caseFlowHandler->retrieveBean($moduleName, $object_id);
                list($flowData['cas_delegate_date'], $flowData['cas_due_date']) = $this->beanHandler->calculateDueDate($eventCriteria, $bean);
                //$this->bpmLog('INFO', "[$cas_id][$newCasIndex] schedule a timer event for $dueDate");                
            }
            $result = $this->prepareResponse($flowData, 'SLEEP', 'CREATE');
        } else {
            /*$flowDueDate = new DateTime($flowData['cas_due_date']);
            $casDueDate = $flowDueDate->getTimestamp();
            $evaluatedCondition = $this->getCurrentTime() > $casDueDate;

            if ($evaluatedCondition) {*/
                $isEventBased = $this->checkIfUsesAnEventBasedGateway($flowData['cas_id'], $flowData['cas_previous']);
                $this->checkIfExistEventBased($flowData['cas_id'], $flowData['cas_previous'], $isEventBased);
                $result = $this->prepareResponse($flowData, 'ROUTE', 'UPDATE');
            /*} else {
                $result = $this->prepareResponse($flowData, 'SLEEP', 'NONE');
            }*/
        }
        return $result;
    }

    
}
