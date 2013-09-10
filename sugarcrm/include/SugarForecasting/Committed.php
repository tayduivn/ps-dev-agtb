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
     * No longer used but the class parent implements SugarForecasting_ForecastProcessInterface
     *
     * @return array|string
     */
    public function process()
    {
        return array_values($this->dataArray);
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

        if (!isset($args['timeperiod_id']) || empty($args['timeperiod_id'])) {
            $args['timeperiod_id'] = TimePeriod::getCurrentId();
        }

        $commit_type = strtolower($this->getArg('commit_type'));

        /* @var $mgr_worksheet ForecastManagerWorksheet */
        $mgr_worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        /* @var $worksheet ForecastWorksheet */
        $worksheet = BeanFactory::getBean('ForecastWorksheets');

        $field_ext = '_case';

        if ($commit_type == "manager") {
            $worksheet_totals = $mgr_worksheet->worksheetTotals($current_user->id, $args['timeperiod_id']);
            // we don't need the *_case values so lets make them the same as the *_adjusted values
            $field_ext = '_adjusted';
        } else {
            $worksheet_totals = $worksheet->worksheetTotals($args['timeperiod_id'], $current_user->id);
            // set likely
            $worksheet_totals['likely_case'] = SugarMath::init($worksheet_totals['amount'], 6)
                    ->add($worksheet_totals['includedClosedAmount'])->result();
            $worksheet_totals['best_case'] = SugarMath::init($worksheet_totals['best_case'], 6)
                    ->add($worksheet_totals['includedClosedBest'])->result();
            $worksheet_totals['worst_case'] = SugarMath::init($worksheet_totals['worst_case'], 6)
                    ->add($worksheet_totals['includedClosedWorst'])->result();
        }
        
        /* @var $forecast Forecast */
        $forecast = BeanFactory::getBean('Forecasts');
        $forecast->user_id = $current_user->id;
        $forecast->timeperiod_id = $args['timeperiod_id'];
        $forecast->best_case = $worksheet_totals['best' . $field_ext];
        $forecast->likely_case = $worksheet_totals['likely' . $field_ext];
        $forecast->worst_case = $worksheet_totals['worst' . $field_ext];
        $forecast->forecast_type = $args['forecast_type'];
        $forecast->opp_count = $worksheet_totals['included_opp_count'];
        $forecast->currency_id = '-99';
        $forecast->base_rate = '1';
        
        //If we are committing a rep forecast, calculate things.  Otherwise, for a manager, just use what is passed in.
        if ($args['commit_type'] == 'sales_rep') {
            $forecast->calculatePipelineData(
                $worksheet_totals['includedClosedAmount'],
                $worksheet_totals['includedClosedCount']
            );
            //push the pipeline numbers back into the args
            $args['pipeline_opp_count'] = $forecast->pipeline_opp_count;
            $args['pipeline_amount'] = $forecast->pipeline_amount;
        } else {
            $forecast->pipeline_opp_count = $worksheet_totals['pipeline_opp_count'];
            $forecast->pipeline_amount = $worksheet_totals['pipeline_amount'];
            $forecast->closed_amount = $worksheet_totals['closed_amount'];
        }
       
        if ($worksheet_totals['likely_case'] != 0 && $worksheet_totals['included_opp_count'] != 0) {
            $forecast->opp_weigh_value = $worksheet_totals['likely_case'] / $worksheet_totals['included_opp_count'];
        }
        $forecast->save();

        // roll up the committed forecast to that person manager view
        $mgr_worksheet->reporteeForecastRollUp($current_user, $args);

        if ($this->getArg('commit_type') == "sales_rep") {
            $worksheet->commitWorksheet($current_user->id, $args['timeperiod_id']);
        } elseif ($this->getArg('commit_type') == "manager") {
            $mgr_worksheet->commitManagerForecast($current_user, $args['timeperiod_id']);
        }

        //TODO-sfa remove this once the ability to map buckets when they get changed is implemented (SFA-215).
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts');
        if (!isset($settings['has_commits']) || !$settings['has_commits']) {
            $admin->saveSetting('Forecasts', 'has_commits', true, 'base');
        }

        $forecast->date_entered = $this->convertDateTimeToISO($db->fromConvert($forecast->date_entered, 'datetime'));
        $forecast->date_modified = $this->convertDateTimeToISO($db->fromConvert($forecast->date_modified, 'datetime'));

        return $worksheet_totals;
    }
}
