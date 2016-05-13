<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once 'include/SugarQuery/SugarQuery.php';
require_once 'modules/pmse_Inbox/engine/PMSEPreProcessor/PMSEPreProcessor.php';
require_once 'modules/pmse_Inbox/engine/PMSELogger.php';

use Sugarcrm\Sugarcrm\ProcessManager;
use Sugarcrm\Sugarcrm\ProcessManager\Registry;

class PMSEHookHandler
{
    /**
     * The PMSE Request object
     * @var PMSERequest
     */
    protected $request;

    /**
     * The PMSE Preprocessor object
     * @var PMSEPreProcessor
     */
    protected $preProcessor;

    /**
     * The PMSE Logger object
     * @var PMSELogger
     */
    protected $logger;

    /**
     * Sugar logger object
     * @var LoggerManager
     */
    protected $sugarLogger;

    /**
     * The ProcessManager Registry object
     * @var Registry
     */
    protected $registry;

    /**
     * List of reasons that processes could be disabled
     * @var array
     */
    protected $disablers = [
        'setup' => 'Perform Setup',
        'upgrade' => 'Upgrade',
    ];

    /**
     * Object constructor
     */
    public function __construct()
    {
        $this->request = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $this->request->setType('hook');
        $this->preProcessor = PMSEPreProcessor::getInstance();
        $this->logger = PMSELogger::getInstance();
        $this->sugarLogger = LoggerManager::getLogger();
        $this->registry = Registry\Registry::getInstance();
    }

    /**
     * Executes a request
     * @param array $args Arguments used in handling the request
     * @param boolean $createThread Whether to create a new thread for processes
     * @param SugarBean $bean Affected bean, if there is one
     * @param string $externalAction Additional action to take
     * @return void
     */
    public function executeRequest($args = array(), $createThread = false, $bean = null, $externalAction = '')
    {
        // If we are disabled we need to bail immediately
        if (!$this->isEnabled()) {
            return;
        }

        // Set the necessary request properties
        $this->request->setArguments($args);
        $this->request->setCreateThread($createThread);
        $this->request->setBean($bean);
        $this->request->setExternalAction($externalAction);

        // Preprocessor doesn't actually return anything
        return $this->preProcessor->processRequest($this->request);
    }

    /**
     *
     * @global type $db
     * @global type $redirectBeforeSave
     * @param type $bean
     * @param type $event
     * @param type $arguments
     * @param type $startEvents
     * @param type $isNewRecord
     * @return boolean
     */
    public function runStartEventAfterSave($bean, $event, $arguments)
    {
        // If we are disabled we need to bail immediately
        if (!$this->isEnabled()) {
            return;
        }

        $this->logger->info("Executing Before save for bean module {$bean->module_name}, with id {$bean->id}");
        $this->executeRequest($arguments, false, $bean, '');
    }


    public function terminateCaseAfterDelete($bean, $event, $arguments)
    {
        // If we are disabled we need to bail immediately
        if (!$this->isEnabled()) {
            return;
        }

        $this->logger->info("Executing Terminate Case for a deleted bean module {$bean->module_name}, with id {$bean->id}");
        $this->executeRequest($arguments, false, $bean, 'TERMINATE_CASE');
    }

    /**
     * Execute the cron tasks.
     */
    public function executeCron()
    {
        // If we are disabled we need to bail immediately
        if (!$this->isEnabled()) {
            return;
        }

        $this->logger->info("Executing PMSE scheduled tasks");
        $this->wakeUpSleepingFlows();
    }

    /**
     * Execute all the flows marked as SLEEPING
     */
    protected function wakeUpSleepingFlows()
    {
        // Needed for the query
        $today = TimeDate::getInstance()->nowDb();

        // We will need this for quoting strings
        $db = DBManagerFactory::getInstance();

        // Used in the get full list process
        $addedSQL = 'bpmn_type = ' . $db->quoted('bpmnEvent') .
                    ' AND cas_flow_status = ' . $db->quoted('SLEEPING') .
                    ' AND cas_due_date <= ' . $db->quoted($today);

        $bean = BeanFactory::getBean('pmse_BpmFlow');
        $flows = $bean->get_full_list('', $addedSQL);

        // If there were flows to process, handle that
        if ($flows !== null && ($c = count($flows)) > 0) {
            foreach ($flows as $flow) {
                $this->newFollowFlow($flow->fetched_row, false, null, 'WAKE_UP');
            }

            $this->logger->info("Processed $c flows with status sleeping");
        } else {
            $this->logger->info("No flows processed with status sleeping");
        }
    }

    protected function newFollowFlow($flowData, $createThread = false, $bean = null, $externalAction = '')
    {
        $fr = ProcessManager\Factory::getPMSEObject('PMSEExecuter');
        return $fr->runEngine($flowData, $createThread, $bean, $externalAction);
    }

    /**
     * Writes a log message to the sugar logger and the PMSE logger. Used primarily
     * on installation and on upgrade.
     * @param string $msg The message to log
     */
    protected function writeLog($msg)
    {
        $this->logger->alert($msg);
        $this->sugarLogger->error($msg);
    }

    /**
     * Checks to see if processes are enabled
     * @return boolean
     */
    protected function isEnabled()
    {
        foreach ($this->disablers as $type => $by) {
            $key = "$type:disable_processes";
            $d = $this->registry->get($key);
            if ($d !== null && $d !== false) {
                $this->writeLog("Process workflows are currently disabled by $by.");
                return false;
            }
        }

        return true;
    }
}
