<?php
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

require_once('include/SugarForecasting/AbstractForecast.php');
class SugarForecasting_Committed extends SugarForecasting_AbstractForecast implements SugarForecasting_ForecastSaveInterface
{
    /**
     * Run all the tasks we need to process get the data back
     *
     * @return array|string
     */
    public function process()
    {
        $this->loadCommitted();

        return array_values($this->dataArray);
    }

    /**
     * Load the Committed Values for someones forecast
     *
     * @return void
     */
    protected function loadCommitted()
    {
        $db = DBManagerFactory::getInstance();

        $args = $this->getArgs();

        $where = "forecasts.user_id = '{$args['user_id']}' AND forecasts.forecast_type='{$args['forecast_type']}' AND forecasts.timeperiod_id = '{$args['timeperiod_id']}'";

        $order_by = 'forecasts.date_modified DESC';
        if (isset($args['order_by'])) {
            $order_by = clean_string($args['order_by']);
        }

        $bean = BeanFactory::getBean('Forecasts');
        $query = $bean->create_new_list_query($order_by, $where, array(), array(), $args['include_deleted']);
        $results = $db->query($query);

        $forecasts = array();
        while (($row = $db->fetchByAssoc($results))) {
            $row['date_entered'] = $this->convertDateTimeToISO($db->fromConvert($row['date_entered'],'datetime'));
            $row['date_modified'] = $this->convertDateTimeToISO($db->fromConvert($row['date_modified'],'datetime'));
            $forecasts[] = $row;
        }

        $this->dataArray = $forecasts;
    }

    /**
     * Save any committed values
     *
     * @return array|mixed
     */
    public function save()
    {
        global $current_user;

        $args = $this->getArgs();
        $db = DBManagerFactory::getInstance();
        
        $args['opp_count'] = !isset($args['opp_count']) ? 0 : $args['opp_count'];
        $args['includedClosedAmount'] = !isset($args['includedClosedAmount']) ? 0 : $args['includedClosedAmount'];
        $args['includedClosedCount'] = !isset($args['includedClosedCount']) ? 0 : $args['includedClosedCount'];
        $args['lost_amount'] = !isset($args['lost_amount']) ? 0 : $args['lost_amount'];
        $args['pipeline_opp_count'] = !isset($args['pipeline_opp_count']) ? 0 : $args['pipeline_opp_count'];
        $args['pipeline_amount'] = !isset($args['pipeline_amount']) ? 0 : $args['pipeline_amount'];

        /* @var $forecast Forecast */
        $forecast = BeanFactory::getBean('Forecasts');
        $forecast->user_id = $current_user->id;
        $forecast->timeperiod_id = $args['timeperiod_id'];
        $forecast->best_case = $args['best_case'];
        $forecast->likely_case = $args['likely_case'];
        $forecast->worst_case = $args['worst_case'];
        $forecast->forecast_type = $args['forecast_type'];
        $forecast->opp_count = $args['opp_count'];
        $forecast->currency_id = $args['currency_id'];
        $forecast->base_rate = $args['base_rate'];
        
        //If we are committing a rep forecast, calculate things.  Otherwise, for a manager, just use what is passed in.
        if ($args['pipeline_opp_count'] == 0 && $args['pipeline_amount'] == 0) {
            $forecast->calculatePipelineData(($args['includedClosedAmount']), ($args['includedClosedCount']));
        } else {
            $forecast->pipeline_opp_count = $args['pipeline_opp_count'];
            $forecast->pipeline_amount = $args['pipeline_amount'];
        } 
       
        if ($args['likely_case'] != 0 && $args['opp_count'] != 0) {
            $forecast->opp_weigh_value = $args['likely_case'] / $args['opp_count'];
        }
        $forecast->save();

        // roll up the committed forecast to that person manager view
        /* @var $mgr_worksheet ForecastManagerWorksheet */
        $mgr_worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $mgr_worksheet->reporteeForecastRollUp($current_user, $args);

        // ForecastWorksheets Table Commit Version
        $data = array(
            'user_id' => $current_user->id,
            'timeperiod_id' => $args['timeperiod_id']
        );

        $timedate = TimeDate::getInstance();
        /* @var $job SchedulersJob */
        $job = BeanFactory::getBean('SchedulersJobs');
        $job->execute_time = $timedate->nowDb();
        $job->name = "Update ForecastWorksheets";
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->target = "class::SugarJobUpdateForecastWorksheets";
        $job->data = json_encode($data);
        $job->retry_count = 0;
        $job->assigned_user_id = $current_user->id;
        $job->save();

        $mgr_worksheet->commitManagerForecast($current_user, $args['timeperiod_id']);

        //TODO-sfa remove this once the ability to map buckets when they get changed is implemented (SFA-215).
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts');
        if (!isset($settings['has_commits']) || !$settings['has_commits']) {
            $admin->saveSetting('Forecasts', 'has_commits', true, 'base');
        }

        $forecast->date_entered = $this->convertDateTimeToISO($db->fromConvert($forecast->date_entered, 'datetime'));
        $forecast->date_modified = $this->convertDateTimeToISO($db->fromConvert($forecast->date_modified, 'datetime'));

        return $forecast->toArray(true);
    }
}