<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

require_once 'modules/SchedulersJobs/SchedulersJob.php';

class SugarTestSchedulersJobUtilities
{
    private static $_createdJobs = array();

    /**
     * Create a new job.
     *
     * @param string $id
     * @return SugarBean
     */
    public static function createJob($id = '')
    {
        $job = BeanFactory::newBean('SchedulersJobs');

        if (!empty($id)) {
            $job->new_with_id = true;
            $job->id = $id;
        }
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        DBManagerFactory::getInstance()->commit();
        self::$_createdJobs[] = $job;
        return $job;
    }

    /**
     * Add a job to the internal storage.
     *
     * @param array $ids
     */
    public static function setCreatedJob($ids)
    {
        foreach ($ids as $jobId) {
            $job = BeanFactory::newBean('SchedulersJobs');
            $job->id = $jobId;
            self::$_createdJobs[] = $job;
        }
    }

    /**
     * Remove jobs, created by this helper, from DB.
     */
    public static function removeAllCreatedJobs()
    {
        $jobIds = self::getCreatedJobIds();
        DBManagerFactory::getInstance()
            ->query('DELETE FROM job_queue WHERE id IN (\'' . implode("', '", $jobIds) . '\')');
    }

    /**
     * Return all IDs of all created records.
     *
     * @return array
     */
    public static function getCreatedJobIds()
    {
        $jobIds = array();
        foreach (self::$_createdJobs as $job) {
            $jobIds[] = $job->id;
        }
        return $jobIds;
    }
}
