<?php
//SUGARCRM FILE flav=ent ONLY

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

array_push($job_strings, 'PMSEEngineCron');

function PMSEEngineCron()
{
    require_once ("modules/pmse_Inbox/engine/PMSEHandlers/PMSEHookHandler.php");

    $hookHandler = new PMSEHookHandler();
    $hookHandler->executeCron();
    return true;
}


function PMSEJobRun ($job) {
    require_once 'modules/pmse_Inbox/engine/PMSEFlowRouter.php';
    require_once 'modules/pmse_Inbox/engine/PMSEHandlers/PMSECaseFlowHandler.php';

    if (!empty($job->data)) {
        $flowData = (array)json_decode($job->data);
        $externalAction = 'RESUME_EXECUTION';
        $jobQueueHandler = new PMSEJobQueueHandler();
        return ($jobQueueHandler->executeRequest($flowData, FALSE, null, $externalAction));
    }
    return false;
}

