<?php
//FILE SUGARCRM flav=pro ONLY
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

require_once('modules/SchedulersJobs/SchedulersJob.php');

/**
 * SugarJobUpdateOpportunities.php
 *
 * Class to run a job which should upgrade every old opp with commit stage, date_closed_timestamp,
 * best/worst cases and related product
 */
class SugarJobUpdateOpportunities implements RunnableSchedulerJob {

    /**
     * @var SchedulersJob
     */
    protected $job;

    /**
     * @param SchedulersJob $job
     */
    public function setJob(SchedulersJob $job)
    {
        $this->job = $job;
    }

    /**
     * @param $data
     * @return bool
     */
    public function run($data)
    {
        $this->job->runnable_ran = true;
        $this->job->runnable_data = $data;

        $data = json_decode($data, true);

        Activity::disable();
        $ftsInstance = SugarSearchEngineFactory::getInstance();
        $ftsInstance->setForceAsyncIndex(true);

        foreach ($data as $row) {
            /* @var $opp Opportunity */
            $opp = BeanFactory::getBean('Opportunities', $row['id']);
            $opp->save(false);
        }

        $ftsInstance->setForceAsyncIndex(
            SugarConfig::getInstance()->get('search_engine.force_async_index', false)
        );
        Activity::enable();

        $this->job->succeedJob();
        return true;
    }

    /**
     * This function creates a job for to run the SugarJobUpdateOpportunities class
     * @param integer $perJob
     * @returns array|string An array of the jobs that were created, unless there
     * is one, then just that job's id
     */
    public static function updateOpportunitiesForForecasting($perJob = 100)
    {
        $sq = new SugarQuery();
        $sq->select(array('id'));
        $sq->from(BeanFactory::getBean('Opportunities'));
        $sq->orderBy('date_closed');

        $rows = $sq->execute();

        $chunks = array_chunk($rows, $perJob);

        $jobs = array();
        // process the first job now
        $job = static::createJob($chunks[0], true);
        $jobs[] = $job->id;
        // run the first job
        $self = new self();
        $self->setJob($job);
        $self->run($job->data);

        for ($i = 1; $i < count($chunks); $i++) {
            $jobs[] = static::createJob($chunks[$i]);
        }

        // if only one job was created, just return that id
        if (count($jobs) == 1) {
            return array_shift($jobs);
        }

        return $jobs;
    }

    /**
     * @param array $data The data for the Job
     * @param bool $returnJob When `true` the job will be returned, otherwise the job id will be returned
     * @return SchedulersJob|String
     */
    public static function createJob(array $data, $returnJob = false)
    {
        global $current_user;

        /* @var $job SchedulersJob */
        $job = BeanFactory::getBean('SchedulersJobs');
        $job->name = "Update Old Opportunities";
        $job->target = "class::SugarJobUpdateOpportunities";
        $job->data = json_encode($data);
        $job->retry_count = 0;
        $job->assigned_user_id = $current_user->id;
        require_once('include/SugarQueue/SugarJobQueue.php');
        $job_queue = new SugarJobQueue();
        $job_queue->submitJob($job);

        if ($returnJob === true) {
            return $job;
        }

        return $job->id;
    }
}
