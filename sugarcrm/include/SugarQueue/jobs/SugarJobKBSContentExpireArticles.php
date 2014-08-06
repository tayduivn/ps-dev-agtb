<?php
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

class SugarJobKBSContentExpireArticles implements RunnableSchedulerJob
{

    /**
     * @var $job Job object.
     */
    protected $job;

    /**
     * Sets the SchedulersJob instance for the class.
     *
     * @param SchedulersJob $job
     */
    public function setJob(SchedulersJob $job)
    {
        $this->job = $job;
    }

    /**
     * Handles processing SchedulersJobs.
     *
     * @param Mixed $data Passed in from the job_queue.
     * @return bool True on success, false on error.
     */
    public function run($data)
    {
        $articles = $this->getArticles();
        foreach ($articles as $article) {
            $bean = BeanFactory::getBean('KBSContents', $article['id']);
            $bean->status = 'expired';
            $bean->save();
        }
        return $this->job->succeedJob();
    }

    /**
     * Returns expired published articles.
     *
     * @return array Of IDs.
     */
    protected function getArticles()
    {
        $td = new TimeDate();
        $sq = new SugarQuery();
        $sq->select(array('id'));
        $sq->from(BeanFactory::getBean('KBSContents'));
        $sq->where()
            ->in('status', array('published-in', 'published-ex', 'published'))
            ->gte('exp_date', $td->nowDbDate());
        return $sq->execute();
    }
}
