<?php

require_once 'PMSEEndEvent.php';
require_once 'modules/pmse_Inbox/engine/PMSEHandlers/PMSEEmailHandler.php';

/**
 * Description of PMSEEndSendMessageEvent
 *
 */

class PMSEEndSendMessageEvent extends PMSEEndEvent
{

    /**
     *
     * @var type 
     */
    protected $loggerMock;

    /**
     *
     * @var type 
     */
    protected $definitionBean;

    /**
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        $this->definitionBean = BeanFactory::getBean('pmse_BpmEventDefinition');
        parent::__construct();

    }

    /**
     * @param $id
     * @return array
     * @codeCoverageIgnore
     */
    public function retrieveDefinitionData($id)
    {
        $this->definitionBean->retrieve($id);
        return ($this->definitionBean->toArray());
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
    public function run($flowData, $bean = null, $externalAction = '', $arguments = array())
    {
        $definitionData = $this->retrieveDefinitionData($flowData['bpmn_id']);
        
        $json = htmlspecialchars_decode($definitionData['evn_params']);
        $addresses = $this->emailHandler->processEmailsFromJson($bean, $json, $flowData);
        $template_id = $definitionData['evn_criteria'];
        $result = $this->emailHandler->sendTemplateEmail($flowData['cas_sugar_module'], $flowData['cas_sugar_object_id'], $addresses, $template_id);

        //if ($result['result']) {
            //$this->bpmLog('DEBUG', "[$cas_id][$cas_index] email sent using template $template_id");
        //} else {
            //$this->bpmLog('ERROR', "[$cas_id][$cas_index] error sending email: " . $result['ErrorInfo']);
        //}

        $flowData['cas_flow_status'] = 'CLOSED';
        return $this->prepareResponse($flowData, 'ROUTE', 'CREATE');
        //$bean = $this->retrieveBean("{$flowData['cas_sugar_module']}", "{$flowData['cas_sugar_object_id']}");
        // close all the status as described before
    }

}
