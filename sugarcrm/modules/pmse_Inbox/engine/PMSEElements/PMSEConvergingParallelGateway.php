<?php

require_once 'PMSEConvergingGateway.php';

class PMSEConvergingParallelGateway extends PMSEConvergingGateway
{
    public function run($flowData, $bean = null, $externalAction = '', $arguments = array())
    {
        $routeAction = 'WAIT';
        $flowAction = 'NONE';
        $filters = array();
        $previousFlows = $this->retrievePreviousFlows('PASSED', $flowData['bpmn_id'], $flowData['cas_id']);
        $totalFlows = $this->retrievePreviousFlows('ALL', $flowData['bpmn_id']);
        if (sizeof($previousFlows) === sizeof($totalFlows)) {
            $routeAction = 'ROUTE';
            $flowAction = 'CREATE';
        }
        return $this->prepareResponse($flowData, $routeAction, $flowAction, $filters);
    }

}
