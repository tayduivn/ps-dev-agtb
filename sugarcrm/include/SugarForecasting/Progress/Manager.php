<?php
/********************************************************************************
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

require_once("include/SugarForecasting/Manager.php");
class SugarForecasting_Progress_Manager extends SugarForecasting_Manager
{
    /**
     * @var Opportunity
     */
    protected $opportunity;
    
    /**
     * @var pipelineCount
     */
     protected $pipelineCount;
     
     /**
     * @var pipelineRevenue
     */
     protected $pipelineRevenue;
     
     /**
      * @var closedAmount
      */
     protected $closedAmount;     
     
    /**
     * Class Constructor
     * @param array $args       Service Arguments
     */
    public function __construct($args)
    {
        parent::__construct($args);

        $this->loadConfigArgs();
    }

    /**
     * Get Settings from the Config Table.
     */
    public function loadConfigArgs() {
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts');

        // decode and json decode the settings from the administration to set the sales stages for closed won and closed lost
        $this->setArg('sales_stage_won', $settings["sales_stage_won"]);
        $this->setArg('sales_stage_lost', $settings["sales_stage_lost"]);
    }

    /**
     * Process the code to return the values that we need
     *
     * @return array
     */
    public function process()
    {
        return $this->getManagerProgress();
    }

    /**
     * Get the Numbers for the Manager View
     *
     * @return array
     */
    public function getManagerProgress()
    {
        //get the quota data for user
        /* @var $quota Quota */
        $quota = BeanFactory::getBean('Quotas');

        //grab user that is the target of this call to check if it is the top level manager
        $targetedUser = BeanFactory::getBean("Users", $this->getArg('user_id'));

        //top level manager has to receive special treatment, but all others can be routed through quota function.
        if ($targetedUser->reports_to_id != "") {
            $quotaData = $quota->getRollupQuota($this->getArg('timeperiod_id'), $this->getArg('user_id'), true);
        } else {
            $quotaData["amount"] = $this->getQuotaTotalFromData();
        }

        //Get pipeline total and count;
        $this->getPipelineRevenue();

        //get data
        $progressData = array(
            "closed_amount"     => $this->closedAmount,
            "opportunities"     => $this->pipelineCount,
            "pipeline_revenue"  => $this->pipelineRevenue,
            "quota_amount"      => isset($quotaData["amount"]) ? ($quotaData["amount"]) : 0
        );

        $user_id = $this->getArg('user_id');
        $timeperiod_id = $this->getArg('timeperiod_id');

        $mgr_worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $totals = $mgr_worksheet->worksheetTotals($user_id, $timeperiod_id);

        $totals['user_id'] = $user_id;
        $totals['timeperiod_id'] = $timeperiod_id;
        // unset some vars that come from the worksheet to avoid confusion with correct data
        // coming from this endpoint for progress
        unset($totals['pipeline_opp_count'], $totals['quota'], $totals['included_opp_count'], $totals['pipeline_amount']);

        // combine totals in with other progress data
        $progressData = array_merge($progressData, $totals);

        return $progressData;
    }

    /**
     * utilizes some of the functions from the base manager class to load data and sum the quota figures
     * @return float
     */
    public function getQuotaTotalFromData()
    {
        //getting quotas from quotas table
        /* @var $db DBManager */
        $db = DBManagerFactory::getInstance();
        $quota_query = "SELECT sum(q.amount/q.base_rate) quota
                        FROM quotas q
                        INNER JOIN users u
                        ON q.user_id = u.id
                        WHERE u.deleted = 0 AND u.status = 'Active'
                            AND q.timeperiod_id = '{$this->getArg('timeperiod_id')}'
                            AND ((u.id = '{$this->getArg('user_id')}' and q.quota_type = 'Direct')
                            OR (u.reports_to_id = '{$this->getArg('user_id')}' and q.quota_type = 'Rollup'))
                            AND q.deleted = 0";

        $row = $db->fetchOne($quota_query);
        return $row['quota'];
    }    

    /**
     * retrieves the amount of opportunities with count less the closed won/lost stages
     *
     */
    public function getPipelineRevenue()
    {

        $db = DBManagerFactory::getInstance();
        $amountSum = 0;
        $query = "";

        $user_id = $this->getArg('user_id');
        $timeperiod_id = $this->getArg('timeperiod_id');
        $excluded_sales_stages_won = $this->getArg('sales_stage_won');
        $excluded_sales_stages_lost = $this->getArg('sales_stage_lost');
        $repIds = User::getReporteeReps($user_id);
        $mgrIds = User::getReporteeManagers($user_id);
        $arrayLen = 0;
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts');
        
        $tableName = strtolower($settings['forecast_by']);
        $tableName = $tableName == 'revenuelineitems' ? 'revenue_line_items' : $tableName;
        $amountColumn = $tableName == 'revenue_line_items' ? 'likely_case' : 'amount';

        //Note: this will all change in sugar7 to the filter API
        //set up outer part of the query
        $query = "select sum(amount) as amount, sum(recordcount) as recordcount, sum(closed) as closed from(";
        
        //build up two subquery strings so we can unify the sales stage loops
        //all manager opps 
        $queryMgrOpps = "SELECT " .
                            "sum(o.{$amountColumn}/o.base_rate) AS amount, count(*) as recordcount, 0 as closed " .
                        "FROM {$tableName} o " .
                        "INNER JOIN users u  " .
                            "ON o.assigned_user_id = u.id " .
                        "INNER JOIN timeperiods t " .
                            "ON t.id = {$db->quoted($timeperiod_id)} " . 
                        "WHERE " .
                            "o.assigned_user_id = {$db->quoted($user_id)} " .                            
                            "AND o.deleted = 0 " .
                            "AND t.start_date_timestamp <= o.date_closed_timestamp " . 
                            "AND t.end_date_timestamp >= o.date_closed_timestamp ";
        
        $queryClosedMgrOpps = "SELECT " .
                                  "0 as amount, 0 as recordcount, sum(o.{$amountColumn}/o.base_rate) AS closed " .
                              "FROM {$tableName} o " .
                              "INNER JOIN users u  " .
                                  "ON o.assigned_user_id = u.id " .
                              "INNER JOIN timeperiods t " .
                                  "ON t.id = {$db->quoted($timeperiod_id)} " . 
                              "WHERE " .
                                  "o.assigned_user_id = {$db->quoted($user_id)} " .                            
                                  "AND o.deleted = 0 " .
                                  "AND t.start_date_timestamp <= o.date_closed_timestamp " . 
                                  "AND t.end_date_timestamp >= o.date_closed_timestamp ";
        
        //only committed direct reportee (manager) opps
        $queryRepOpps = "";
        $arrayLen = count($mgrIds);
        for($index = 0; $index < $arrayLen; $index++) {
            $subQuery = "(select (pipeline_amount / base_rate) as amount, " .
                                 "pipeline_opp_count as recordcount, " .
                                 "(closed_amount / base_rate) as closed from forecasts " .
                         "where timeperiod_id = {$db->quoted($timeperiod_id)} " .
                            "and user_id = {$db->quoted($mgrIds[$index])} " .
                            "and forecast_type = 'Rollup' " .
                         "order by date_entered desc ";
            $queryRepOpps .= $db->limitQuery($subQuery, 0, 1, false, "", false);
            $queryRepOpps .= ") ";
            if ($index+1 != $arrayLen) {
                $queryRepOpps .= "union all ";
            }
        }
        
        $arrayLen = count($repIds);
        
        //if we've started adding queries, we need a union to pick up the rest if we have more to add
        if ($queryRepOpps != "" && $arrayLen > 0) {
            $queryRepOpps .= " union all ";
        }
        //only committed direct reportee (manager) opps
        for($index = 0; $index < $arrayLen; $index++) {
            $subQuery = "(select (pipeline_amount / base_rate) as amount, " .
                                 "pipeline_opp_count as recordcount, " .
                                 "(closed_amount / base_rate) as closed from forecasts " .
                         "where timeperiod_id = {$db->quoted($timeperiod_id)} " .
                            "and user_id = {$db->quoted($repIds[$index])} " .
                            "and forecast_type = 'Direct' " .
                         "order by date_entered desc ";
            $queryRepOpps .= $db->limitQuery($subQuery, 0, 1, false, "", false);
            $queryRepOpps .= ") ";
            if ($index+1 != $arrayLen) {
                $queryRepOpps .= "union all ";
            }
        }
        
        //per requirements, exclude the sales stages won from amount, but find them for the closed total
        if (count($excluded_sales_stages_won)) {
            foreach ($excluded_sales_stages_won as $exclusion) {
                $queryMgrOpps .= "AND o.sales_stage != {$db->quoted($exclusion)} ";                
            }
            $queryClosedMgrOpps .= "AND o.sales_stage IN ('" . implode("', '", $excluded_sales_stages_won) . "') ";
        }       

        //per the requirements, exclude the sales stages for closed lost
        if (count($excluded_sales_stages_lost)) {
            foreach ($excluded_sales_stages_lost as $exclusion) {
                $queryMgrOpps .= "AND o.sales_stage != {$db->quoted($exclusion)} ";
            }
        }
        
        //Union the two together if we have two separate queries
        $query .= $queryMgrOpps . " union all " . $queryClosedMgrOpps;
        if ($queryRepOpps != "") {
            $query .= " union all " . $queryRepOpps;
        }
        
        //finally, finish up the outer query
        $query .= ") sums";
        
        $result = $db->query($query);
        $row = $db->fetchByAssoc($result);
        $this->pipelineRevenue = is_numeric($row["amount"]) ? $row["amount"] : 0;
        $this->pipelineCount = is_numeric($row["recordcount"]) ? $row["recordcount"] : 0;
        $this->closedAmount = is_numeric($row["closed"]) ? $row["closed"] : 0;
    }
}
