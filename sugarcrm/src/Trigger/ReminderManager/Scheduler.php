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

namespace Sugarcrm\Sugarcrm\Trigger\ReminderManager;

/**
 * Class Scheduler manages reminders by scheduler jobs.
 * It sets SchedulersJob for user from Call or Meeting.
 * When time comes the @see \Sugarcrm\Sugarcrm\Trigger\Job\ReminderJob::run() will be called.
 *
 * For setting up reminders use @see Scheduler::addReminderForUser()
 * For deleting reminders use @see Scheduler::deleteReminders()
 *
 * @package Sugarcrm\Sugarcrm\Trigger\ReminderManager
 */
class Scheduler extends Base
{
    const CALLBACK_CLASS = 'class::\Sugarcrm\Sugarcrm\Trigger\Job\ReminderJob';

    /**
     * @var \SugarJobQueue
     */
    private $jobQueue;

    /**
     * @inheritdoc
     */
    public function deleteReminders(\SugarBean $eventBean)
    {
        $tag = $this->makeTag($eventBean);
        $jobBean = $this->getSchedulersJob();
        $query = $this->getSugarQuery();

        $query->from($jobBean);
        $query->where()->contains('job_group', $tag);
        $beans = $jobBean->fetchFromQuery($query);

        foreach ($beans as $job) {
            /* @var $job \Call|\Meeting */
            $job->mark_deleted($job->id);
        }
    }

    /**
     * @inheritdoc
     */
    public function addReminderForUser(\SugarBean $bean, \User $user, \DateTime $reminderTime)
    {
        /* @var $job \SchedulersJob */
        $job = \BeanFactory::newBean('SchedulersJobs');
        /* @var $bean \Call|\Meeting */
        $job->name = 'Reminder Job ' . $bean->name;
        $job->job_group = $this->makeTag($bean) . ':' . $this->makeTag($user);
        $job->data = json_encode($this->prepareTriggerArgs($bean, $user));
        $job->target = static::CALLBACK_CLASS;

        $job->execute_time = $this->getTimeDate()->asDb($reminderTime, false);
        $job->requeue = true;
        $this->getSugarJobQueue()->submitJob($job);
    }

    /**
     * @inheritdoc
     */
    protected function makeTag($bean)
    {
        return md5(parent::makeTag($bean));
    }

    /**
     * Factory method for \SchedulersJob class.
     *
     * @return \SchedulersJob
     * @codeCoverageIgnore
     */
    protected function getSchedulersJob()
    {
        return \BeanFactory::getBean('SchedulersJobs');
    }

    /**
     * Factory method for \SugarJobQueue class.
     *
     * @return \SugarJobQueue
     * @codeCoverageIgnore
     */
    protected function getSugarJobQueue()
    {
        if (!$this->jobQueue) {
            $this->jobQueue = new \SugarJobQueue();
        }

        return $this->jobQueue;
    }

    /**
     * Access method for \TimeDate object.
     * @return \TimeDate
     * @codeCoverageIgnore
     */
    protected function getTimeDate()
    {
        return \TimeDate::getInstance();
    }

    /**
     * Factory method for \SugarQuery class.
     *
     * @return \SugarQuery
     * @codeCoverageIgnore
     */
    protected function getSugarQuery()
    {
        return new \SugarQuery();
    }
}
