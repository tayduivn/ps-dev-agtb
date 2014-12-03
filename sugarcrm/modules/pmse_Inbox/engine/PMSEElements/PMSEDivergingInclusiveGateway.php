<?php
require_once 'PMSEDivergingGateway.php';

class PMSEDivergingInclusiveGateway extends PMSEDivergingGateway
{
    /**
     *
     * @param type $flowData
     * @param type $bean
     * @param type $externalAction
     * @return type
     */
    public function run($flowData, $bean = null, $externalAction = '', $arguments = array())
    {
        $flowAction = 'CREATE';
        $filters = $this->filterFlows(
            'ALL',
            $this->retrieveFollowingFlows($flowData),
            $bean,
            $flowData
        );

        $routeAction = empty($filters) ? 'WAIT' : 'ROUTE';

        return $this->prepareResponse($flowData, $routeAction, $flowAction, $filters);
    }
}
