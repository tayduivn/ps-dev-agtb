<?php

require_once 'PMSEGateway.php';


class PMSEConvergingGateway extends PMSEGateway
{
    public function retrievePreviousFlows($type, $elementId, $casId = '')
    {
        $sugarQuery = $this->retrieveSugarQueryObject();
        $flowBean = $this->caseFlowHandler->retrieveBean('pmse_BpmnFlow');
        
        $sugarQuery->select(array('a.id'));
        $sugarQuery->from($flowBean, array('alias' => 'a'));
        
        switch ($type){
            case 'PASSED':
                $joinClause = 'INNER JOIN';
                $whereClause = 'b.bpmn_type=\'bpmnFlow\' AND b.cas_id=\''.$casId.'\' AND';
                break;
            case 'ALL':
            default:
                $joinClause = 'LEFT JOIN';
                $whereClause = '';
                break;
        };

        $sugarQuery->joinRaw("{$joinClause} pmse_bpm_flow b ON (a.id = b.bpmn_id)", array('alias'=>'b'));
        $sugarQuery->where()->queryAnd()
            ->addRaw("{$whereClause} a.flo_element_dest='{$elementId}' AND a.flo_element_dest_type='bpmnGateway'");
        $flows = $sugarQuery->execute();

        $filteredFlows = array();
        foreach ($flows as $element) {
            $filteredFlows[] = $element['id'];
        }
        $filteredFlows = array_unique($filteredFlows);
        return $filteredFlows;
    }
    //put your code here
}
