<?php
require_once 'PMSEFlow.php';

class PMSESequenceFlow extends PMSEFlow
{
    /**
     * Run implementation for a bpm flow element
     * @param array $flowData
     * @param type $bean
     * @param type $externalAction
     * @return type
     * @codeCoverageIgnore
     */
    /*
        public function run($flowData, $bean = null, $externalAction = '', $arguments = array())
        {
            $accessManager = new PMSEAccessManagement();
            $activeLicense = $accessManager->expirationKey();
    //        $activeLicense=lookLicenced::look();
            $routeStatus = $activeLicense ? 'ROUTE' : 'FREEZE';
            $flowData['cas_flow_status'] = $routeStatus;
            return $this->prepareResponse($flowData, $routeStatus, 'CREATE');
        }
    */
}
