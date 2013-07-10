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

require_once('include/SugarForecasting/Chart/AbstractChart.php');
require_once('include/SugarForecasting/Manager.php');
class SugarForecasting_Chart_Manager extends SugarForecasting_Chart_AbstractChart
{
    /**
     * Constructor
     *
     * @param array $args
     */
    public function __construct($args)
    {
        $this->isManager = true;

        if (isset($args['data_array']) && $args['data_array']) {
            $this->dataArray = $args['data_array'];
        }

        parent::__construct($args);

        if (!is_array($this->dataset)) {
            $this->dataset = array($this->dataset);
        }
    }

    /**
     * Process the data into the current JIT Chart Format
     *
     * @return array
     */
    public function process()
    {
        return $this->generateChartJson();
    }

    public function generateChartJson()
    {
        $config = $this->getForecastConfig();
        // sort the data so it's in the correct order
        usort($this->dataArray, array($this, 'sortChartColumns'));

        // loop variables
        $values = array();

        foreach ($this->dataArray as $data) {
            $value = array(
                'id' => $data['id'],
                'user_id' => $data['user_id'],
                'name' => html_entity_decode($data['name'], ENT_QUOTES),
                'likely' => SugarCurrency::convertWithRate($data['likely_case'], $data['base_rate']),
                'likely_adjusted' => SugarCurrency::convertWithRate(
                    $data['likely_case_adjusted'],
                    $data['base_rate']
                )
            );

            if ($config['show_worksheet_best']) {
                $value['best'] = SugarCurrency::convertWithRate($data['best_case'], $data['base_rate']);
                $value['best_adjusted'] = SugarCurrency::convertWithRate(
                    $data['best_case_adjusted'],
                    $data['base_rate']
                );
            }
            if ($config['show_worksheet_worst']) {
                $value['worst'] = SugarCurrency::convertWithRate($data['worst_case'], $data['base_rate']);
                $value['worst_adjusted'] = SugarCurrency::convertWithRate(
                    $data['worst_case_adjusted'],
                    $data['base_rate']
                );
            }
            $values[] = $value;
        }

        $forecast_strings = $this->getModuleLanguage('Forecasts');
        global $app_strings;

        $tp = $this->getTimeperiod();

        return array(
                'title' => string_format(
                    $forecast_strings['LBL_CHART_FORECAST_FOR'],
                    array($tp->name)
                ),
                'quota' => $this->getRollupQuota(),
                'labels' => array(
                    'dataset' => array(
                        'likely' => $app_strings['LBL_LIKELY'],
                        'best' => $app_strings['LBL_BEST'],
                        'worst' => $app_strings['LBL_WORST'],
                        'likely_adjusted' => $app_strings['LBL_LIKELY_ADJUSTED'],
                        'best_adjusted' => $app_strings['LBL_LIKELY_ADJUSTED'],
                        'worst_adjusted' => $app_strings['LBL_LIKELY_ADJUSTED']
                    )
                ),
            'data' => $values
        );
    }

    /**
     * Get the quota from the sum of all the rows in the dataset
     *
     * @return float
     */
    protected function getQuotaTotalFromData()
    {
        $quota = 0;

        foreach ($this->dataArray as $data) {
            $quota += SugarCurrency::convertAmountToBase($data['quota'], $data['currency_id']);
        }

        return $quota;
    }

    /**
     * Get the roll up quota for a manager from the quota table.  If one doesn't exist it
     * will call @see getQuotaTotalFromData to return the quota total from the worksheet dataset
     *
     * @return float
     */
    protected function getRollupQuota()
    {
        //get the quota data for user
        /* @var $quota Quota */
        $quota = BeanFactory::getBean('Quotas');

        //grab user that is the target of this call to check if it is the top level manager
        $targetedUser = BeanFactory::getBean("Users", $this->getArg('user_id'));

        if (!empty($targetedUser->reports_to_id)) {
            $quotaData = $quota->getRollupQuota($this->getArg('timeperiod_id'), $this->getArg('user_id'), true);
            return SugarCurrency::convertAmountToBase($quotaData["amount"], $quotaData['currency_id']);
        }
        // get the quota from the loaded data for a manager that has no manager
        return $this->getQuotaTotalFromData();


    }

    /**
     * Method for sorting the dataArray before we return it so that the tallest bar is always first and the
     * lowest bar is always last.
     *
     * @param array $a          The left side of the compare
     * @param array $b          The right side of the compare
     * @return int
     */
    protected function sortChartColumns($a, $b)
    {
        $sumA = 0;
        $sumB = 0;

        foreach ($this->dataset as $dataset) {
            $sumA += SugarCurrency::convertAmountToBase($a[$dataset . '_case_adjusted'], $a['currency_id']);
            $sumB += SugarCurrency::convertAmountToBase($b[$dataset . '_case_adjusted'], $b['currency_id']);
        }

        if (intval($sumA) > intval($sumB)) {
            return -1;
        } else {
            if (intval($sumA) < intval($sumB)) {
                return 1;
            } else {
                return 0;
            }
        }
    }
}
