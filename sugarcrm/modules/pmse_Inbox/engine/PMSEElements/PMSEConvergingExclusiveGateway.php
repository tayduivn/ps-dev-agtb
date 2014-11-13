<?php

require_once 'PMSEConvergingGateway.php';

class PMSEConvergingExclusiveGateway extends PMSEConvergingGateway
{
    public function run($flowData, $bean = null, $externalAction = '', $arguments = array())
    {
        $routeAction = 'WAIT';
        $flowAction = 'NONE';
        $filters = array();
        $previousFlows = $this->retrievePreviousFlows('PASSED', $flowData['bpmn_id'], $flowData['cas_id']);
        if (sizeof($previousFlows) === 1) {
            $routeAction = 'ROUTE';
            $flowAction = 'CREATE';
        }
        return $this->prepareResponse($flowData, $routeAction, $flowAction, $filters);
    }
}
