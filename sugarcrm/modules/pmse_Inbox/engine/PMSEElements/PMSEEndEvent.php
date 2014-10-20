<?php

require_once 'modules/pmse_Inbox/engine/PMSEHandlers/PMSECaseFlowHandler.php';
require_once 'PMSEEvent.php';

class PMSEEndEvent extends PMSEEvent
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
        //return parent::run($flowData, $bean);
        
        //global $db;
//        parent::execute($flowData, $bean);
//        $this->bpmLog('DEBUG', "[$cas_id][$cas_index] execute " . ($bpmnElement['evn_marker']) . " End Event");

        //get the bean loaded, because we need it to process email addresses
        
        //close the thread
        $this->caseFlowHandler->closeThreadByCaseIndex($flowData['cas_id'], $flowData['cas_previous']);
        //close this flow
        //$this->setStartDateInCaseFlow($cas_id, $cas_index);
        //$this->setCloseStatusInCaseFlow($cas_id, $cas_index);
        //check if there are more threads
        $query = "select count(*) as open from  pmse_bpm_thread where cas_id = {$flowData['cas_id']} and cas_thread_status = 'OPEN' ";
        $result = $bean->db->Query($query);
        $row = $bean->db->fetchByAssoc($result);
        if (is_array($row)) {
            //if no, close the entire case (terminate the case)
            if ($row['open'] == 0) {                
                $this->caseFlowHandler->closeCase($flowData['cas_id']);
            }
        }
        $flowData['cas_flow_status'] = 'CLOSED';
        return $this->prepareResponse($flowData, 'ROUTE', 'CREATE');
    }
}
