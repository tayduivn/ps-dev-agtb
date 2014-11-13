<?php


require_once 'PMSEDivergingGateway.php';

class PMSEDivergingEventBasedGateway extends PMSEDivergingGateway
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
        $routeAction = 'ROUTE';
        $flowAction = 'CREATE';
        $nonFlowElements = $this->getNextShapeElements($flowData);        

        foreach ($nonFlowElements as $element) {
            if ($element['evn_type'] != 'INTERMEDIATE' || $element['evn_behavior'] != 'CATCH' || empty($element)) {
                $routeAction = 'WAIT';
            }
        }

        return $this->prepareResponse($flowData, $routeAction, $flowAction);
    }
}
