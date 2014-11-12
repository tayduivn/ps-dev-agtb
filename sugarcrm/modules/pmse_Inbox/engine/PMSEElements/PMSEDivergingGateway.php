<?php

require_once 'PMSEGateway.php';

class PMSEDivergingGateway extends PMSEGateway
{

    /**
     * @param type $flowData
     */
    public function retrieveFollowingFlows($flowData)
    {
        $bpmnFlowBean = $this->caseFlowHandler->retrieveBean('pmse_BpmnFlow');
        $where = "flo_element_origin_type = 'bpmnGateway' and flo_element_origin = '{$flowData['bpmn_id']}' ";
        $orderBy = 'flo_type DESC, flo_eval_priority ASC';
        $rows = $bpmnFlowBean->get_full_list($orderBy, $where);
        return $rows;
    }
    
    /**
     * 
     * @param type $flow
     * @param type $bean
     * @param type $flowData
     * @return boolean
     */
    public function evaluateFlow($flow, $bean, $flowData)
    {
        if ($flow->flo_type === 'DEFAULT') {
            //$this->bpmLog('INFO', "[$cas_id][$cas_index] following the default flow");
            return true;
        }
        
        if ($flow->flo_condition == '') {
            return false;
        }

        $params = array('db' => $this->getDbHandler(), 'cas_id' => $flowData['cas_id']);
        $resultEvaluation = $this->expressionEvaluator->evaluateExpression($flow->flo_condition, $bean, $params);
        return $resultEvaluation;
    }
    
    /**
     * 
     * @param type $type
     * @param type $flows
     * @param type $bean
     * @param type $flowData
     * @return array
     */
    public function filterFlows($type, $flows, $bean, $flowData)
    {
        $filters = array();
        foreach ($flows as $flow) {
            $resultEvaluation = $this->evaluateFlow($flow, $bean, $flowData);
            $this->logger->info("Evaluate returned " . ($resultEvaluation ? 'true' : 'false'));
            if ($resultEvaluation) {
                //$this->bpmLog('INFO', "[$cas_id][$cas_index] next flow is " . $flow->flo_element_dest_type . "-" . $flow->flo_element_dest);
                array_push($filters, $flow->id);
                if ($type === 'SINGLE') {
                    break;
                }
            }
        }

        return $filters;
    }
}
