<?php
//FILE SUGARCRM flav=pro ONLY
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('modules/SchedulersJobs/SchedulersJob.php');

/**
 * SugarJobUpdateForecastWorksheets
 *
 * Class to run a job which will create the ForecastWorksheet entries for the timeperiod and user
 *
 */
class SugarJobUpdateForecastWorksheets implements RunnableSchedulerJob
{

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
     * @param string $data The job data set for this particular Scheduled Job instance
     * @return boolean true if the run succeeded; false otherwise
     */
    public function run($data)
    {

        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts');

        if ($settings['is_setup'] == false) {
            $GLOBALS['log']->fatal("Forecast Module is not setup. " . __CLASS__ . " should not be running");
            return false;
        }

        $args = json_decode(html_entity_decode($data), true);
        $this->job->runnable_ran = true;

        if (empty($args['timeperiod_id']) || empty($args['user_id'])) {
            $GLOBALS['log']->fatal("Unable to run job due to missing arguments");
            return false;
        }

        /* @var $tp TimePeriod */
        $tp = BeanFactory::getBean('TimePeriods', $args['timeperiod_id']);

        if (empty($tp->id)) {
            $GLOBALS['log']->fatal("Unable to load TimePeriod for id: " . $args['timeperiod_id']);
            return false;
        }

        $type = ucfirst($settings['forecast_by']);

        $sq = new SugarQuery();
        $sq->from(BeanFactory::getBean($type))->where()
            ->equals('assigned_user_id', $args['user_id'])
            ->queryAnd()
                ->gte('date_closed_timestamp', $tp->start_date_timestamp)
                ->lte('date_closed_timestamp', $tp->end_date_timestamp);
        $beans = $sq->execute();

        foreach ($beans as $bean) {
            /* @var $obj Opportunity|Product */
            $obj = BeanFactory::getBean($type);
            $obj->loadFromRow($bean);

            /* @var $worksheet ForecastWorksheet */
            $worksheet = BeanFactory::getBean('ForecastWorksheets');
            if ($type == 'Opportunities') {
                $worksheet->saveRelatedOpportunity($obj, true);
                //BEGIN SUGARCRM flav=ent ONLY
                // for opps we need to commit any products attached to them
                $worksheet->saveOpportunityProducts($obj, true);
                //END SUGARCRM flav=ent ONLY

            } elseif ($type == 'Products') {
                $worksheet->saveRelatedProduct($obj, true);
            }

        }

        $this->job->succeedJob();
        return true;
    }

}