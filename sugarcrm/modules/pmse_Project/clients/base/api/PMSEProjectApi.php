<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'clients/base/api/ModuleApi.php';
require_once 'data/BeanFactory.php';

require_once 'wrappers/PMSEProjectWrapper.php';
require_once 'wrappers/PMSECrmDataWrapper.php';
require_once 'wrappers/PMSEActivityDefinitionWrapper.php';
require_once 'wrappers/PMSEEventDefinitionWrapper.php';
require_once 'wrappers/PMSEGatewayDefinitionWrapper.php';
require_once 'wrappers/PMSEDynaForm.php';
require_once 'wrappers/PMSEObservers/PMSEEventObserver.php';
require_once 'wrappers/PMSEObservers/PMSEProcessObserver.php';

require_once 'modules/pmse_Inbox/engine/PMSEProjectImporter.php';
require_once 'modules/pmse_Inbox/engine/PMSEProjectExporter.php';

class PMSEProjectApi extends ModuleApi
{
    private $projectWrapper;
    private $crmDataWrapper;
    private $activityDefinitionWrapper;
    private $eventDefinitionWrapper;
    private $gatewayDefinitionWrapper;

    public function __construct()
    {
        $this->projectWrapper = new PMSEProjectWrapper();
        $this->crmDataWrapper = new PMSECrmDataWrapper();
        $this->activityDefinitionWrapper = new PMSEActivityDefinitionWrapper();
        $this->eventDefinitionWrapper = new PMSEEventDefinitionWrapper();
        $this->gatewayDefinitionWrapper = new PMSEGatewayDefinitionWrapper();
    }

    /**
     *
     * @return type
     */
    public function registerApiRest()
    {
        return array(
            'createProject' => array(
                'reqType' => 'POST',
                'path' => array('pmse_Project'),
                'pathVars' => array('module'),
                'method' => 'createProject',
                'shortHelp' => 'This method updates a record of the specified type',
                'longHelp' => 'include/api/help/module_record_put_help.html',
            ),
            'updateProject' => array(
                'reqType' => 'PUT',
                'path' => array('pmse_Project', '?'),
                'pathVars' => array('module', 'record'),
                'method' => 'updateProject',
                'shortHelp' => 'This method updates a record of the specified type',
                'longHelp' => 'include/api/help/module_record_put_help.html',
            ),
            'readCustomProject' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Project', 'project', '?'),
                'pathVars' => array('module', 'customAction', 'record'),
                'method' => 'retrieveCustomProject',
                'shortHelp' => 'This method updates a record of the specified type',
                'longHelp' => 'include/api/help/module_record_put_help.html',
            ),
            'updateCustomProject' => array(
                'reqType' => 'PUT',
                'path' => array('pmse_Project', 'project', '?'),
                'pathVars' => array('module', 'customAction', 'record'),
                'method' => 'updateCustomProject',
                'shortHelp' => 'This method updates a record of the specified type',
                'longHelp' => 'include/api/help/module_record_put_help.html',
            ),
            'readCrmData' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Project', 'CrmData', '?', '?'),
                'pathVars' => array('module', '', 'data', 'filter'),
                'method' => 'getCrmData',
                'shortHelp' => 'Get the CrmData from the backend',
                'longHelp' => 'modules/ProcessMaker/api/help/project_get_help.html',
                //'noLoginRequired' => true
            ),
            'updateCrmData' => array(
                'reqType' => 'PUT',
                'path' => array('pmse_Project', 'CrmData', '?', '?'),
                'pathVars' => array('module', '', 'record', 'filter'),
                'method' => 'putCrmData',
                'shortHelp' => 'Put data to the backend',
                'longHelp' => 'modules/ProcessMaker/api/help/project_get_help.html',
                //'noLoginRequired' => true
            ),
            'readCrmDataWithoutFilters' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Project', 'CrmData', '?'),
                'pathVars' => array('module', '', 'data'),
                'method' => 'getCrmData',
                'shortHelp' => 'Get the CrmData from the backend',
                'longHelp' => 'modules/ProcessMaker/api/help/project_get_help.html',
                //'noLoginRequired' => true
            ),
            'readActivityDefinition' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Project', 'ActivityDefinition', '?'),
                'pathVars' => array('module', '', 'record'),
                'method' => 'getActivityDefinition',
                'shortHelp' => 'Get the CrmData from the backend',
                'longHelp' => 'modules/ProcessMaker/api/help/project_get_help.html',
                //'noLoginRequired' => true
            ),
            'updateActivityDefinition' => array(
                'reqType' => 'PUT',
                'path' => array('pmse_Project', 'ActivityDefinition', '?'),
                'pathVars' => array('module', '', 'record'),
                'method' => 'putActivityDefinition',
                'shortHelp' => 'Put the CrmData to the backend',
                'longHelp' => 'modules/ProcessMaker/api/help/project_get_help.html',
                //'noLoginRequired' => true
            ),
            'readEventDefinition' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Project', 'EventDefinition', '?'),
                'pathVars' => array('module', '', 'record'),
                'method' => 'getEventDefinition',
                'shortHelp' => 'Get the CrmData from the backend',
                'longHelp' => 'modules/ProcessMaker/api/help/project_get_help.html'
            ),
            'updateEventDefinition' => array(
                'reqType' => 'PUT',
                'path' => array('pmse_Project', 'EventDefinition', '?'),
                'pathVars' => array('module', '', 'record'),
                'method' => 'putEventDefinition',
                'shortHelp' => 'Get the CrmData from the backend',
                'longHelp' => 'modules/ProcessMaker/api/help/project_get_help.html'
            ),
            'readGatewayDefinition' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Project', 'GatewayDefinition', '?'),
                'pathVars' => array('module', '', 'record'),
                'method' => 'getGatewayDefinition',
                'shortHelp' => 'Get the CrmData from the backend',
                'longHelp' => 'modules/ProcessMaker/api/help/project_get_help.html'
            ),
            'updateGatewayDefinition' => array(
                'reqType' => 'PUT',
                'path' => array('pmse_Project', 'GatewayDefinition', '?'),
                'pathVars' => array('module', '', 'record'),
                'method' => 'putGatewayDefinition',
                'shortHelp' => 'Get the CrmData from the backend',
                'longHelp' => 'modules/ProcessMaker/api/help/project_get_help.html'
            ),
            'verifyRunningProcess' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Project', '?', 'verify'),
                'pathVars' => array('module', 'record', 'verify'),
                'method' => 'verifyRunningProcess',
                'shortHelp' => 'Get the CrmData from the backend',
                'longHelp' => 'modules/ProcessMaker/api/help/project_get_help.html'
            )
        );
    }

    public function retrieveCustomProject($api, $args)
    {
        //global $current_user;
        $api->action = "read";
        $this->requireArgs($args, array('record'));

        return $this->projectWrapper->retrieveProject($args['record']);
    }

    public function updateCustomProject($api, $args)
    {
        //global $current_user;
        $api->action = "update";
        $this->requireArgs($args, array('record'));

        return $this->projectWrapper->updateProject($args['record'], $args);
    }

    /**
     * Method to be used for my create Project endpoint
     * @global Object $current_user
     * @param type $api
     * @param type $args
     * @return array
     */
    public function createProject($api, $args)
    {
        global $current_user;
        $api->action = 'save';
        $this->requireArgs($args, array('module'));

        $bean = BeanFactory::newBean($args['module']);

        $dynaForm = new PMSEDynaForm();

        $date = new DateTime();

        if (!empty($args['id'])) {
            // Check if record already exists
            if (BeanFactory::getBean(
                $args['module'],
                $args['id'],
                array('strict_retrieve' => true, 'disable_row_level_security' => true)
            )
            ) {
                throw new SugarApiExceptionInvalidParameter(
                    'Record already exists: ' . $args['id'] . ' in module: ' . $args['module']
                );
            }
            // Don't create a new id if passed in
            $bean->new_with_id = true;
        }

        $id = $this->updateBean($bean, $api, $args);

        $args['record'] = $id;

        //retrieve a Bean created
        $projectBean = BeanFactory::retrieveBean($args['module'], $args['record']);

        //Create a Diagram row
        $diagramBean = BeanFactory::newBean('pmse_BpmnDiagram');
        $diagramBean->dia_uid = PMSEEngineUtils::generateUniqueID();
        $diagramBean->name = $projectBean->name;
        $diagramBean->description = $projectBean->description;
        $diagramBean->assigned_user_id = $projectBean->assigned_user_id;
        $diagramBean->prj_id = $id;
        $dia_id = $diagramBean->save();

        //Create a Process row
        $processBean = BeanFactory::newBean('pmse_BpmnProcess');
        $processBean->pro_uid = PMSEEngineUtils::generateUniqueID();
        $processBean->name = $projectBean->name;
        $processBean->description = $projectBean->description;
        $processBean->assigned_user_id = $projectBean->assigned_user_id;
        $processBean->prj_id = $id;
        $processBean->dia_id = $dia_id;
        $pro_id = $processBean->save();

        //Create a ProcessDefinition row
        $processDefinitionBean = BeanFactory::newBean('pmse_BpmProcessDefinition');
        $processDefinitionBean->id = $pro_id;
        $processDefinitionBean->new_with_id = true;
        $processDefinitionBean->prj_id = $id;
        $processDefinitionBean->pro_module = $projectBean->prj_module;
        $processDefinitionBean->pro_status = $projectBean->prj_status;
        $processDefinitionBean->assigned_user_id = $projectBean->assigned_user_id;
        $processDefinitionBean->save();

        //Create Dynaform
        //$dynaFormBean = BeanFactory::getBean('pmse_BpmDynaForm');
        //$dynaFormBean->name = 'Default';
        //$dynaFormBean->description = 'Default';
        //$dynaFormBean->dyn_module = $projectBean->prj_module;
        //$dynaFormBean->prj_id = $id;
        //$dynaFormBean->pro_id = $pro_id;
        //$dynaFormBean->dyn_uid = PMSEEngineUtils::generateUniqueID();
        //$dynaFormBean->save();

        $keysArray = array('prj_id' => $id, 'pro_id' => $pro_id);
        $dynaForm->generateDefaultDynaform($processDefinitionBean->pro_module, $keysArray, false);

        return $this->getLoadedAndFormattedBean($api, $args, $bean);
    }

//    public function updateCustomProject($api, $args) {
//        global $current_user;
//        $api->action = 'save';
//        $this->requireArgs($args,array('module','record'));
//        $response = new stdClass();
//        $response->success = true;
//        $response->args = $args;
//        return $response;
//
//    }

    public function updateProject($api, $args)
    {
        $api->action = 'view';
        $this->requireArgs($args, array('module', 'record'));

        $bean = $this->loadBean($api, $args, 'save');
        $api->action = 'save';
        $this->updateBean($bean, $api, $args);

        $args['pro_module'] = isset($args['prj_module']) ? $args['prj_module'] : null;
        $args['pro_status'] = isset($args['prj_status']) ? $args['prj_status'] : null;

        $observer = new PMSEProcessObserver();
        $this->projectWrapper->attach($observer);
        $this->projectWrapper->updateProcessDefinition($args);
    }

    public function deleteRecord($api, $args)
    {
        $this->requireArgs($args, array('module', 'record'));
        $bean = $this->loadBean($api, $args, 'delete');
        $bean->mark_deleted($args['record']);
        return array('id' => $bean->id);
    }

    /**
     * Shared method from create and update process that handles records that
     * might not pass visibility checks. This method assumes the API has validated
     * the authorization to create/edit records prior to this point.
     *
     * @param ServiceBase $api The service object
     * @param array $args Request arguments
     * @param SugarBean $bean The bean for this process
     * @return array Array of formatted fields
     */
    protected function getLoadedAndFormattedBean($api, $args, SugarBean $bean)
    {
        $addNoAccessAcl = false;
        // Load the bean fresh to ensure the cache entry from the create process
        // doesn't get in the way of visibility checks
        try {
            $bean = $this->loadBean($api, $args, 'view', array('use_cache' => false));
        } catch (SugarApiExceptionNotAuthorized $e) {
            // If there was an exception thrown from the load process then strip
            // the field list down and return only id and date_modified. This will
            // happen on new records created with visibility rules that conflict
            // with the current user or from edits made to records that do the same
            // thing.
            $args['fields'] = 'id,date_modified';
            $addNoAccessAcl = true;
        }

        $api->action = 'view';
        $data = $this->formatBean($api, $args, $bean);

        if ($addNoAccessAcl) {
            $data['_acl'] = array(
                'access' => 'no',
                'view' => 'no',
            );
        }

        return $data;
    }

    /**
     *
     * @param ServiceBase $api
     * @param array $args
     * @return type
     */
    public function getCrmData($api, $args)
    {
        return $this->crmDataWrapper->_get($args, $this);
    }

    /**
     *
     * @param ServiceBase $api
     * @param array $args
     * @return type
     */
    public function putCrmData($api, $args)
    {
        $processObserver = new PMSEProcessObserver();
        $this->crmDataWrapper->attach($processObserver);
        return $this->crmDataWrapper->_put($args);
    }

    /**
     *
     * @param ServiceBase $api
     * @param array $args
     * @return type
     */
    public function getActivityDefinition($api, $args)
    {
        return $this->activityDefinitionWrapper->_get($args);
    }

    public function putActivityDefinition($api, $args)
    {
        return $this->activityDefinitionWrapper->_put($args);
    }

    /**
     *
     * @param ServiceBase $api
     * @param array $args
     * @return type
     */
    public function getEventDefinition($api, $args)
    {
        return $this->eventDefinitionWrapper->_get($args);
    }

    public function putEventDefinition($api, $args)
    {
        $observer = new PMSEEventObserver();
        $this->eventDefinitionWrapper->attach($observer);
        $this->eventDefinitionWrapper->_put($args);
    }

    public function getGatewayDefinition($api, $args)
    {
        return $this->gatewayDefinitionWrapper->_get($args);
    }

    public function putGatewayDefinition($api, $args)
    {
        return $this->gatewayDefinitionWrapper->_put($args);
    }

    public function verifyRunningProcess($api, $args)
    {
        $result = false;
        $projectBean = BeanFactory::getBean($args['module'], $args['record'],
            array('strict_retrieve' => true, 'disable_row_level_security' => true));
        $processBean = BeanFactory::getBean('pmse_BpmnProcess')->retrieve_by_string_fields(array("prj_id" => $projectBean->id));
        $casesBean = BeanFactory::getBean('pmse_Inbox')->retrieve_by_string_fields(array("pro_id" => $processBean->id));
        $values = array('COMPLETED', 'TERMINATED', 'CANCELLED');
        if ($processBean && $casesBean && !in_array($casesBean->cas_status , $values)) {
            $result = true;
        }
        return $result;
    }
}
