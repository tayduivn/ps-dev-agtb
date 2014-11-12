<?php

require_once 'PMSEDivergingGateway.php';

class PMSEDivergingExclusiveGateway extends PMSEDivergingGateway
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
                'SINGLE',
                $this->retrieveFollowingFlows($flowData),
                $bean,
                $flowData
            );
        
         if (empty($filters)) {             
            throw new PMSEElementException('The gateway possibly don\'t have any configuration', $flowData, $this);
        } else {
            $routeAction = 'ROUTE';
        }
            
        return $this->prepareResponse($flowData, $routeAction, $flowAction, $filters);
    }
}
