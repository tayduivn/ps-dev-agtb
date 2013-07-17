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
require_once('include/SugarForecasting/Individual.php');
class SugarForecasting_Chart_Individual extends SugarForecasting_Chart_AbstractChart
{
    /**
     * Constructor
     *
     * @param array $args
     */
    public function __construct($args)
    {
        if (isset($args['data_array']) && $args['data_array']) {
            $this->dataArray = $args['data_array'];
        }
        parent::__construct($args);
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

    protected function generateChartJson()
    {
        global $app_list_strings, $app_strings;

        $arrData = array();
        $arrProbabilities = array();
        $forecast_strings = $this->getModuleLanguage('Forecasts');
        $config = $this->getForecastConfig();


        foreach ($this->dataArray as $data) {
            $v = array(
                'id' => $data['id'],
                'forecast' => $data['commit_stage'],
                'probability' => $data['probability'],
                'sales_stage' => $data['sales_stage'],
                'likely' => SugarCurrency::convertWithRate($data['likely_case'], $data['base_rate']),
                'date_closed_timestamp' => intval($data['date_closed_timestamp'])
            );

            if ($config['show_worksheet_best']) {
                $v['best'] = SugarCurrency::convertWithRate($data['best_case'], $data['base_rate']);
            }
            if ($config['show_worksheet_worst']) {
                $v['worst'] = SugarCurrency::convertWithRate($data['worst_case'], $data['base_rate']);
            }

            $arrData[] = $v;

            $arrProbabilities[] = $data['probability'];
        }

        $arrProbabilities = array_unique($arrProbabilities);
        asort($arrProbabilities);

        $tp = $this->getTimeperiod();
        $chart_info = array(
            'title' => string_format(
                $forecast_strings['LBL_CHART_FORECAST_FOR'],
                array($tp->name)
            ),
            'quota' => $this->getUserQuota(),
            'x-axis' => $tp->getChartLabels(array()),
            'labels' => array(
                'forecast' => $app_list_strings[$config['buckets_dom']],
                'sales_stage' => $app_list_strings['sales_stage_dom'],
                'probability' => array_combine($arrProbabilities, $arrProbabilities),
                'dataset' => array(
                    'likely' => $app_strings['LBL_LIKELY'],
                    'best' => $app_strings['LBL_BEST'],
                    'worst' => $app_strings['LBL_WORST']
                )
            ),
            'data' => $arrData
        );

        return $chart_info;
    }

    /**
     * Return the quota for the current user and time period
     *
     * @return mixed
     */
    protected function getUserQuota()
    {
        /* @var $quota_bean Quota */
        $quota_bean = BeanFactory::getBean('Quotas');
        $quota = $quota_bean->getRollupQuota($this->getArg('timeperiod_id'), $this->getArg('user_id'));

        return SugarCurrency::convertAmountToBase($quota['amount'], $quota['currency_id']);
    }
}
