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

require_once 'PMSEValidate.php';
require_once 'modules/pmse_Inbox/engine/PMSELogger.php';

/**
 * Description of PMSEElementValidator
 *
 */
class PMSEElementValidator implements PMSEValidate
{

    /**
     *
     * @var type
     */
    protected $dbHandler;

    /**
     *
     * @var type
     */
    protected $logger;

    /**
     *
     * @global type $db
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        global $db;
        $this->dbHandler = $db;
        $this->logger = PMSELogger::getInstance();
    }

    /**
     *
     * @return type
     * @codeCoverageIgnore
     */
    public function getDbHandler()
    {
        return $this->dbHandler;
    }

    /**
     *
     * @return type
     * @codeCoverageIgnore
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     *
     * @param type $dbHandler
     * @codeCoverageIgnore
     */
    public function setDbHandler($dbHandler)
    {
        $this->dbHandler = $dbHandler;
    }

    /**
     *
     * @param type $logger
     * @codeCoverageIgnore
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     *
     * @param PMSERequest $request
     * @param type $flowData
     * @return PMSERequest
     */
    public function validateRequest(PMSERequest $request)
    {
        $this->logger->info("Validate Request " . get_class($this));
        $this->logger->debug(array("Request data:", $request));

        $flowData = $request->getFlowData();
        $bean = $request->getBean();
        $request->setExternalAction($this->processExternalAction($flowData));
        $request->setCreateThread($this->processCreateThread($flowData));

        switch ($flowData['evn_type']) {
            case 'START':
                $this->logger->info("Validate Start Event.");
                $this->validateStartEvent($bean, $flowData, $request);
                break;
            case 'INTERMEDIATE':
                $this->logger->info("Validate Intermediate Event.");
                $this->validateIntermediateEvent($bean, $flowData, $request);
                break;
            default:
                break;
        }
        return $request;
    }

    /**
     *
     * @param type $flowData
     * @return string
     */
    public function identifyElementStatus($flowData)
    {
        $result = '';
        if (isset($flowData['cas_id']) && isset($flowData['cas_index'])) {
            $result = 'RUNNING';
        } else {
            $result = 'NEW';
        }
        return $result;
    }

    /**
     *
     * @param type $flowData
     * @return string
     */
    public function identifyEventAction($flowData)
    {
        if (isset($flowData['rel_process_module'])
            && isset($flowData['rel_element_relationship'])
            && isset($flowData['rel_element_module'])
            && $flowData['rel_element_module'] !== $flowData['rel_process_module']
        ) {
            return 'EVALUATE_RELATED_MODULE';
        } else {
            return 'EVALUATE_MAIN_MODULE';
        }
    }

    /**
     *
     * @param type $flowData
     * @return boolean
     */
    public function processExternalAction($flowData)
    {
        if ($this->identifyElementStatus($flowData) == 'RUNNING' && $flowData['evn_type'] == 'INTERMEDIATE') {
            return $this->identifyEventAction($flowData);
        } else {
            return false;
        }
    }

    /**
     *
     * @param type $flowData
     * @return boolean
     */
    public function processCreateThread($flowData)
    {
        if ($this->identifyElementStatus($flowData) == 'NEW') {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @param type $bean
     * @return type
     * @codeCoverageIgnore
     */
    public function isNewRecord($bean)
    {
        return empty($bean->fetched_row['id']);
    }

    /**
     *
     * @param type $bean
     * @param type $flowData
     * @return boolean
     */
    public function isCaseDuplicated($bean, $flowData)
    {
        $queryDupli = "select * from pmse_bpm_flow where " .
            "cas_sugar_object_id = '" . $bean->id . "' " .
            "and cas_sugar_module = '" . $bean->module_name . "' " .
            "and cas_index = 1 " .
            "and pro_id = '{$flowData['pro_id']}'";

        $resultDupli = $this->dbHandler->Query($queryDupli);
        $rowDupli = $this->dbHandler->fetchByAssoc($resultDupli);

        if (is_array($rowDupli)) {
            $this->logger->debug("Start Event {$bean->id} already exists");
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @param type $bean
     * @return boolean
     */
    public function isPMSEEdit($bean)
    {
        if (isModuleBWC($_REQUEST['moduleName'])) {
            $url = $_REQUEST['module'];
        } else {
            $url = $_REQUEST['__sugar_url'];
        }

        if (strpos($url, 'pmse') === false) {
            return false;
        } else {
            $this->logger->debug("Start Event {$bean->id} can not be triggered by PMSE modules.");
            return true;
        }
    }

    /**
     *
     * @param type $bean
     * @param type $flowData
     * @return boolean
     */
    public function validateStartEvent($bean, $flowData, $request)
    {
        if ((($this->isNewRecord($bean) && $flowData['evn_params'] == 'new' ||
                !$this->isNewRecord($bean) && $flowData['evn_params'] == 'updated')
                 && !$this->isCaseDuplicated($bean, $flowData)) ||
            !$this->isNewRecord($bean) && $flowData['evn_params'] == 'allupdates' && !$this->isPMSEEdit($bean)
        ) {
            $request->validate();
        } else {
            $request->invalidate();
        }
    }

    /**
     *
     * @param type $bean
     * @param type $flowData
     * @param type $request
     * @return type
     * @codeCoverageIgnore
     */
    public function validateIntermediateEvent($bean, $flowData, $request)
    {
        $request->validate();
        return $request;
    }

}
