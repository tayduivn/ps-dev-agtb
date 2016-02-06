<?php
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


/**
 * Description of PMSEJobQueue
 * The PMSEJobQueue class creates and assign a queued job that executes a BPM
 * element such as a gateway, activity or event.
 *
 */
class PMSEJobQueueHandler extends PMSEAbstractRequestHandler
{
    /**
     * @inheritDoc
     */
    protected $requestType = 'queue';

    /**
     * @var SchedulersJob this attribute stores an instance of the SchedulersJob class
     */
    protected $schedulersJob;

    /**
     * @var SugarJobQueue this attribute stores an instance of the SchedulersJob class
     */
    protected $sugarJobQueue;

    /**
     * @var User this attribute stores an instance of the User class,
     * especificcally the current logged user that executes the process
     */
    protected $currentUser;

    /**
     * Set the Scheduler Job attribute
     * @param SchedulersJob $schedulersJob
     * @codeCoverageIgnore
     */
    public function setSchedulersJob(SchedulersJob $schedulersJob)
    {
        $this->schedulersJob = $schedulersJob;
    }

    /**
     * Retrieve the Scheduler Job attribute
     * @return SchedulersJob
     * @codeCoverageIgnore
     */
    public function getSchedulersJob()
    {
        if (empty($this->schedulersJob)) {
            $this->schedulersJob = new SchedulersJob();
        }

        return $this->schedulersJob;
    }

    /**
     * Set the Sugar Job Queue attribute.
     * @param SugarJobQueue $sugarJobQueue
     * @codeCoverageIgnore
     */
    public function setSugarJobQueue(SugarJobQueue $sugarJobQueue)
    {
        $this->sugarJobQueue = $sugarJobQueue;
    }

    /**
     * Retrieve the Sugar Job Queue attribute
     * @return SugarJobQueue
     * @codeCoverageIgnore
     */
    public function getSugarJobQueue()
    {
        if (empty($this->sugarJobQueue)) {
            $this->sugarJobQueue = new SugarJobQueue();
        }

        return $this->sugarJobQueue;
    }

    /**
     * Set the current User attribute
     * @param User $currentUser
     * @codeCoverageIgnore
     */
    public function setCurrentUser(User $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    /**
     * Get Current User attribute
     * @return User
     * @codeCoverageIgnore
     */
    public function getCurrentUser()
    {
        if (empty($this->currentUser)) {
            $this->currentUser = $this->fetchCurrentUser();
        }

        return $this->currentUser;
    }

    /**
     * Fetches the current user from the global variable
     * @return User
     */
    protected function fetchCurrentUser()
    {
        global $current_user;
        return $current_user;
    }

    /**
     * Submit a Job top the Sugar job queue handler
     * @param type $params
     * @return type
     */
    public function submitPMSEJob($params)
    {
        // Grab our jobber
        $job = $this->getSchedulersJob();

        // Set some properties now
        $job->name = "PMSE Job - {$params->id}";

        //data we are passing to the job
        $job->data = json_encode($this->filterData($params->data));

        //function to call
        $job->target = "function::PMSEJobRun";
        $job->message = "Executing a PMSE queued task.";

        //set the user the job runs as
        $job->assigned_user_id = $this->getCurrentUser()->id;

        //push into the queue to run
        return $this->getSugarJobQueue()->submitJob($job);
    }

    public function filterData($dataArray)
    {
        $validFields = array(
            'evn_criteria',
            'rel_element_module',
            'rel_element_relationship',
            'rel_process_module',
            'new_with_id',
            'cas_delayed',
            'cas_finished',
            'cas_started',
            'cas_delay_duration',
            'cas_duration',
            'cas_queue_duration',
            'cas_due_date',
            'cas_finish_date',
            'cas_start_date',
            'cas_delegate_date',
            'cas_sugar_action',
            'cas_sugar_object_id',
            'cas_sugar_module',
            'cas_flow_status',
            'cas_thread',
            'cas_user_id',
            'bpmn_type',
            'bpmn_id',
            'pro_id',
            'cas_id',
            'cas_index',
            'id'
        );

        foreach ($dataArray as $key => $value) {
            if (!in_array($key, $validFields)) {
                unset($dataArray[$key]);
            }
        }

        return $dataArray;
    }
}
