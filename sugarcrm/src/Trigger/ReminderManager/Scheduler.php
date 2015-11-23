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
 * It sets SchedulersJob for every user from Call or Meeting.
 * Method uses @see \Call::users_arr or @see \Meeting::users_arr as source of users.
 * When time comes the @see \Sugarcrm\Sugarcrm\Trigger\Job\ReminderJob::run() will be called.
 *
 * For setting up reminders use @see Scheduler::setReminders()
 *
 * For deleting reminders use @see Scheduler::deleteReminders()
 *
 * Examples:
 * <code>
 * // instantiate manager
 * $manager = new Scheduler();
 *
 * // set new reminders from Call
 * $manager->setReminders($call, false);
 *
 * // delete old reminders and set new reminders from Call
 * $manager->setReminders($call, true);
 *
 * // delete reminders from Call
 * $manager->deleteReminders($call);
 *
 * </code>
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
    public function setReminders(\SugarBean $bean, $isUpdate)
    {
        if ($isUpdate) {
            $this->deleteReminders($bean);
        }
        $this->addReminders($bean);
    }

    /**
     * @inheritdoc
     */
    public function deleteReminders(\SugarBean $bean)
    {
        $this->deleteByJobGroup($this->makeTag($bean));
    }

    /**
     * @inheritdoc
     */
    public function addReminderForUser(\SugarBean $bean, \User $user)
    {
        $jobQueue = $this->getSugarJobQueue();
        $reminderTime = $this->getReminderTime($bean, $user);

        if ($reminderTime > 0) {
            $job = $this->createSchedulersJob($bean, $user, $reminderTime);
            $jobQueue->submitJob($job);
        }
    }

    /**
     * Adds triggers to scheduler. Method adds one job for every user.
     *
     * @param \Call|\Meeting|\SugarBean $bean
     */
    protected function addReminders(\SugarBean $bean)
    {
        foreach ($this->loadUsers($bean->users_arr) as $user) {
            $this->addReminderForUser($bean, $user);
        }
    }

    /**
     * Creates \SchedulersJob and sets it properties.
     *
     * @param \Call|\Meeting|\SugarBean $bean
     * @param \User $user
     * @param int $reminderTime
     * @return \SchedulersJob
     */
    protected function createSchedulersJob(\SugarBean $bean, \User $user, $reminderTime)
    {
        $job = $this->getSchedulersJob();
        $job->name = 'Reminder Job ' . $bean->name;
        $job->job_group = $this->makeTag($bean) . ':' . $this->makeTag($user);
        $job->data = json_encode($this->prepareTriggerArgs($bean, $user));
        $job->target = static::CALLBACK_CLASS;
        $job->execute_time = $this->getTimeDate()
            ->asDb($this->prepareReminderDateTime($bean->date_start, $reminderTime), false);
        $job->requeue = true;

        return $job;
    }

    /**
     * Removes job from scheduler by job group.
     *
     * @param string $group
     */
    protected function deleteByJobGroup($group)
    {
        $bean = $this->getBean('SchedulersJobs');
        $query = $this->makeLoadRemindersByJobGroupSugarQuery($bean, $group);
        $objects = $bean->fetchFromQuery($query);

        foreach ($objects as $job) {
            $job->mark_deleted($job->id);
        }
    }

    /**
     * Makes SugarQuery for loading scheduler's jobs by group.
     *
     * @param \SchedulersJob $bean
     * @param string $group
     * @return \SugarQuery
     */
    protected function makeLoadRemindersByJobGroupSugarQuery(\SchedulersJob $bean, $group)
    {
        $query = $this->getSugarQuery();
        $query->from($bean);
        $query->where()->contains('job_group', $group);
        return $query;
    }

    /**
     * @inheritdoc
     */
    protected function makeTag($bean)
    {
        return $this->hashTag($this->parentMakeTag($bean));
    }

    /**
     * Creates MD5 hash from tag. It needs to prevent
     * problems with storing and searching jobs in db.
     *
     * @param string $tag
     * @return string
     * @codeCoverageIgnore
     */
    protected function hashTag($tag)
    {
        return md5($tag);
    }

    /**
     * Access method to parent::makeTag()
     *
     * @param \Call|\Meeting|\User|\SugarBean $bean
     * @return string
     * @codeCoverageIgnore
     */
    protected function parentMakeTag(\SugarBean $bean)
    {
        return parent::makeTag($bean);
    }

    /**
     * Factory method for \SchedulersJob class.
     *
     * @return \SchedulersJob
     * @codeCoverageIgnore
     */
    protected function getSchedulersJob()
    {
        return new \SchedulersJob();
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
}
