<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * Handles populating seed data for Forecasts module
 */
class ForecastsSeedData
{

    /**
     * @static
     *
     * @param Array $timeperiods Array of $timeperiod instances to build forecast data for
     */
    public static function populateSeedData($timeperiods)
    {
        require_once('modules/Forecasts/Common.php');

        global $timedate, $current_user, $app_list_strings;

        $user = BeanFactory::getBean('Users');
        $comm = new Common();
        $commit_order = $comm->get_forecast_commit_order();

        // get what we are forecasting on
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts');

        $forecast_by = $settings['forecast_by'];

        $forecast_by = ucfirst(strtolower($forecast_by));

        foreach ($timeperiods as $timeperiod_id => $timeperiod) {

            foreach ($commit_order as $commit_type_array) {
                //create forecast schedule for this timeperiod record and user.
                //create forecast schedule using this record because there will be one
                //direct entry per user, and some user will have a Rollup entry too.

                $ratio = array('.8', '1', '1.2', '1.4');
                $key = array_rand($ratio);

                if ($commit_type_array[1] == 'Direct') {
                    // get the worksheet total for a given user
                    /* @var $worksheet ForecastWorksheet */
                    $worksheet = BeanFactory::getBean('ForecastWorksheets');
                    $totals = $worksheet->worksheetTotals($timeperiod_id, $commit_type_array[0], $forecast_by);

                    if ($totals['total_opp_count'] == 0) {
                        continue;
                    }

                    /* @var $quota Quota */
                    $quota = BeanFactory::getBean('Quotas');
                    $quota->timeperiod_id = $timeperiod_id;
                    $quota->user_id = $commit_type_array[0];
                    $quota->quota_type = 'Direct';
                    $quota->currency_id = -99;

                    $quota->amount = SugarMath::init()->exp('?*?', array($totals['amount'], $ratio[$key]))->result();
                    $quota->amount_base_currency = $quota->amount;
                    $quota->committed = 1;
                    $quota->set_created_by = false;
                    if ($commit_type_array[0] == 'seed_sarah_id' ||
                        $commit_type_array[0] == 'seed_will_id' ||
                        $commit_type_array[0] == 'seed_jim_id'
                    ) {
                        $quota->created_by = 'seed_jim_id';
                    } else {
                        if ($commit_type_array[0] == 'seed_sally_id' || $commit_type_array[0] == 'seed_max_id') {
                            $quota->created_by = 'seed_sarah_id';
                        } else {
                            if ($commit_type_array[0] == 'seed_chris_id') {
                                $quota->created_by = 'seed_will_id';
                            } else {
                                $quota->created_by = $current_user->id;
                            }
                        }
                    }

                    $quota->save();

                    if (!$user->isManager($commit_type_array[0])) {
                        /* @var $quotaRollup Quota */
                        $quotaRollup = BeanFactory::getBean('Quotas');
                        $quotaRollup->timeperiod_id = $timeperiod_id;
                        $quotaRollup->user_id = $commit_type_array[0];
                        $quotaRollup->quota_type = 'Rollup';
                        $quota->currency_id = -99;
                        $quotaRollup->amount = $quota->amount;
                        $quotaRollup->amount_base_currency = $quotaRollup->amount;
                        $quotaRollup->committed = 1;
                        $quotaRollup->set_created_by = false;
                        if ($commit_type_array[0] == 'seed_sarah_id' ||
                            $commit_type_array[0] == 'seed_will_id' ||
                            $commit_type_array[0] == 'seed_jim_id'
                        ) {
                            $quotaRollup->created_by = 'seed_jim_id';
                        } else {
                            if ($commit_type_array[0] == 'seed_sally_id' || $commit_type_array[0] == 'seed_max_id') {
                                $quotaRollup->created_by = 'seed_sarah_id';
                            } else {
                                if ($commit_type_array[0] == 'seed_chris_id') {
                                    $quotaRollup->created_by = 'seed_will_id';
                                } else {
                                    $quotaRollup->created_by = $current_user->id;
                                }
                            }
                        }

                        $quotaRollup->save();
                    }

                    /* @var $forecast Forecast */
                    $forecast = BeanFactory::getBean('Forecasts');
                    $forecast->timeperiod_id = $timeperiod_id;
                    $forecast->user_id = $commit_type_array[0];
                    $forecast->opp_count = $totals['included_opp_count'];
                    if ($totals['included_opp_count'] > 0) {
                        $forecast->opp_weigh_value = SugarMath::init()->exp(
                            '(?/?)/?',
                            array($totals['amount'], $ratio[$key], $totals['included_opp_count'])
                        )->result();
                    } else {
                        $forecast->opp_weigh_value = '0';
                    }
                    $forecast->best_case = SugarMath::init()->exp('?/?', array($totals['best_case'], $ratio[$key]))->result();
                    $forecast->worst_case = SugarMath::init()->exp('?/?', array($totals['worst_case'], $ratio[$key]))->result();
                    $forecast->likely_case = SugarMath::init()->exp('?/?', array($totals['amount'], $ratio[$key]))->result();
                    $forecast->forecast_type = 'Direct';
                    $forecast->date_committed = $timedate->asDb($timedate->getNow()->modify("-1 day"));
                    $forecast->calculatePipelineData(
                        SugarMath::init()->exp('?/?', array($totals['includedClosedAmount'], $ratio[$key]))->result(),
                        $totals['includedClosedCount']
                    );
                    $forecast->save();

                    self::createManagerWorksheet($commit_type_array[0], $forecast->toArray());

                    //Create a previous forecast to simulate change
                    /* @var $forecast2 Forecast */
                    $forecast2 = BeanFactory::getBean('Forecasts');
                    $forecast2->timeperiod_id = $timeperiod_id;
                    $forecast2->user_id = $commit_type_array[0];
                    $forecast2->opp_count = $totals['included_opp_count'];
                    if ($totals['included_opp_count'] > 0) {
                        $forecast2->opp_weigh_value = SugarMath::init()->exp(
                            '?/?',
                            array($totals['amount'], $totals['included_opp_count'])
                        )->result();
                    } else {
                        $forecast2->opp_weigh_value = '0';
                    }
                    $forecast2->best_case = $totals['best_case'];
                    $forecast2->worst_case = $totals['worst_case'];
                    $forecast2->likely_case = $totals['amount'];
                    $forecast2->forecast_type = 'Direct';
                    $forecast2->date_committed = $timedate->asDb($timedate->getNow());
                    $forecast2->calculatePipelineData(
                        $totals['includedClosedAmount'],
                        $totals['includedClosedCount']
                    );
                    $forecast2->save();

                    self::createManagerWorksheet($commit_type_array[0], $forecast2->toArray());

                } else {

                    /* @var $mgr_worksheet ForecastManagerWorksheet */
                    $mgr_worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
                    $totals = $mgr_worksheet->worksheetTotals($timeperiod_id, $commit_type_array[0]);

                    if ($totals['included_opp_count'] == 0) {
                        continue;
                    }

                    /* @var $quota Quota */
                    $quota = BeanFactory::getBean('Quotas');
                    $quota->timeperiod_id = $timeperiod_id;
                    $quota->user_id = $commit_type_array[0];
                    $quota->quota_type = 'Rollup';
                    $quota->currency_id = -99;
                    $quota->amount = SugarMath::init($totals['quota'], 6)->mul($ratio[$key])->result();
                    $quota->amount_base_currency = $quota->amount;
                    $quota->committed = 1;
                    $quota->save();

                    /* @var $forecast Forecast */
                    $forecast = BeanFactory::getBean('Forecasts');
                    $forecast->timeperiod_id = $timeperiod_id;
                    $forecast->user_id = $commit_type_array[0];
                    $forecast->opp_count = $totals['included_opp_count'];
                    $forecast->opp_weigh_value = SugarMath::init()->exp(
                        '?/?',
                        array($totals['likely_adjusted'], $totals['included_opp_count'])
                    )->result();
                    $forecast->likely_case = $totals['likely_adjusted'];
                    $forecast->best_case = $totals['best_adjusted'];
                    $forecast->worst_case = $totals['worst_adjusted'];
                    $forecast->forecast_type = 'Rollup';
                    $forecast->pipeline_opp_count = $totals['pipeline_opp_count'];
                    $forecast->pipeline_amount = $totals['pipeline_amount'];
                    $forecast->date_entered = $timedate->asDb($timedate->getNow());
                    $forecast->save();

                    self::createManagerWorksheet($commit_type_array[0], $forecast->toArray());

                }

                self::commitRepItems($commit_type_array[0], $timeperiod_id, $forecast_by);
            }

            // loop though all the managers and commit their forecast
            $managers = array(
                'seed_sarah_id',
                'seed_will_id',
                'seed_jim_id' // we do jim last since sarah and will will feed up into jim
            );

            foreach ($managers as $manager) {
                /* @var $user User */
                $user = BeanFactory::getBean('Users', $manager);
                /* @var $worksheet ForecastManagerWorksheet */
                $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
                $worksheet->commitManagerForecast($user, $timeperiod_id);
            }
        }


        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('Forecasts', 'is_setup', 1, 'base');

        // TODO-sfa - remove this once the ability to map buckets when they get changed is implemented (SFA-215).
        // this locks the forecasts ranges configs if the apps is installed with demo data and already has commits
        $admin->saveSetting('Forecasts', 'has_commits', 1, 'base');
    }

    protected static function createManagerWorksheet($user_id, $data)
    {
        /* @var $user User */
        $user = BeanFactory::getBean('Users', $user_id);
        /* @var $worksheet ForecastManagerWorksheet */
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $worksheet->reporteeForecastRollUp($user, $data);
    }

    protected static function commitRepItems($user_id, $timeperiod, $forecast_by)
    {
        /* @var $tp TimePeriod */
        $tp = BeanFactory::getBean('TimePeriods', $timeperiod);

        $sq = new SugarQuery();
        $sq->from(BeanFactory::getBean($forecast_by))->where()
            ->equals('assigned_user_id', $user_id)
            ->queryAnd()
            ->gte('date_closed_timestamp', $tp->start_date_timestamp)
            ->lte('date_closed_timestamp', $tp->end_date_timestamp);
        $beans = $sq->execute();

        foreach ($beans as $bean) {
            /* @var $obj Opportunity|Product */
            $obj = BeanFactory::getBean($forecast_by);
            $obj->loadFromRow($bean);

            /* @var $opp_wkst ForecastWorksheet */
            $opp_wkst = BeanFactory::getBean('ForecastWorksheets');
            if ($forecast_by == 'Opportunities') {
                $opp_wkst->saveRelatedOpportunity($obj, true);
            } else {
                $opp_wkst->saveRelatedProduct($obj, true);
            }
        }
    }
}
