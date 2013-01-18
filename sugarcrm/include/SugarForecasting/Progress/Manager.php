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
        //create opportunity to use to build queries
        $this->opportunity = new Opportunity();

        //get the quota data for user
        /* @var $quota Quota */
        $quota = BeanFactory::getBean('Quotas');

        //grab user that is the target of this call to check if it is the top level manager
        $targetedUser = BeanFactory::getBean("Users", $this->getArg('user_id'));

        //top level manager has to receive special treatment, but all others can be routed through quota function.
        if($targetedUser->reports_to_id != "") {
            $quotaData = $quota->getRollupQuota($this->getArg('timeperiod_id'), $this->getArg('user_id'), true);
        } else {
            $quotaData["amount"] = $this->getQuotaTotalFromData();
        }

        //Get pipeline total and count;
        $this->getPipelineRevenue();

        //get data
		$progressData = array(
            "closed_amount"     => $this->getClosedAmount(),
            "opportunities"     => $this->pipelineCount,
            "pipeline_revenue"  => $this->pipelineRevenue,
            "quota_amount"      => isset($quotaData["amount"]) ? ($quotaData["amount"]) : 0
		);

		return $progressData;
    }

    /**
     * utilizes some of the functions from the base manager class to load data and sum the quota figures
     * @return float
     */
    public function getQuotaTotalFromData()
    {
        try {
            $this->loadUsers();
        } catch (SugarForecasting_Exception $sfe) {
            return "";
        }

        $this->loadUsersQuota();
        $this->loadWorksheetAdjustedValues();

        $quota = 0;

        foreach ($this->dataArray as $data) {
            $quota += SugarCurrency::convertAmountToBase($data['quota'], $data['currency_id']);
        }

        return $quota;

    }

    /**
     * retreives the number of opportunities set to be used in this forecast period, excludes only the closed stages
     *
     * @return mixed
     */
    public function getPipelineOpportunityCount()
    {
        $db = DBManagerFactory::getInstance();

        $user_id = $this->getArg('user_id');
        $timeperiod_id = $this->getArg('timeperiod_id');
        $excluded_sales_stages_won = $this->getArg('sales_stage_won');
        $excluded_sales_stages_lost = $this->getArg('sales_stage_lost');

        //set user ids and timeperiods
        $where = "( users.reports_to_id = " . $db->quoted($user_id);
        $where .= " OR opportunities.assigned_user_id = " . $db->quoted($user_id) . ")";
        $where .= " AND timeperiods.id = " . $db->quoted($timeperiod_id);


        //per requirements, exclude the sales stages won
        if (count($excluded_sales_stages_won)) {
            foreach ($excluded_sales_stages_won as $exclusion) {
                $where .= " AND opportunities.sales_stage != " . $db->quoted($exclusion);
            }
        }

        //per the requirements, exclude the sales stages for closed lost
        if (count($excluded_sales_stages_lost)) {
            foreach ($excluded_sales_stages_lost as $exclusion) {
                $where .= " AND opportunities.sales_stage != " . $db->quoted($exclusion);
            }
        }

        // no deleted opportunities
        $where .= " AND opportunities.deleted = 0";

        //build the query
        $query = $this->opportunity->create_list_query(NULL, $where);
        $query = $this->opportunity->create_list_count_query($query);

        $result = $db->query($query);
        $row = $db->fetchByAssoc($result);
        $opportunitiesCount = $row['c'];

        return $opportunitiesCount;
    }


    /**
     * Retrieves the amount of closed won opportunities
     *
     * @return int
     */
    public function getClosedAmount()
    {
        $db = DBManagerFactory::getInstance();
        
        $amountSum = 0;
        
        $user_id = $this->getArg('user_id');
        $timeperiod_id = $this->getArg('timeperiod_id');
        $sales_stage_won = $this->getArg('sales_stage_won');

        //set user ids and timeperiods
        $query = "SELECT sum(o.amount * o.base_rate) AS amount FROM opportunities o INNER JOIN users u ";
        $query .= " ON o.assigned_user_id = u.id ";
        $query .= " left join timeperiods t ";
        $query .= " ON t.start_date_timestamp <= o.date_closed_timestamp ";
        $query .= " AND t.end_date_timestamp >= o.date_closed_timestamp ";
        $query .= " WHERE t.id = " . $db->quoted($timeperiod_id);
        $query .= " AND o.deleted = 0 AND (u.reports_to_id = " . $db->quoted($user_id);
        $query .= " OR o.assigned_user_id = " . $db->quoted($user_id) . ")";

        //pre requirements, include only closed won opportunities
        if (!empty($sales_stage_won)) {
            $query .= " AND o.sales_stage in ( '";
            $query .= join("','", $sales_stage_won) . "')";
        }

        $result = $db->query($query);

        $row = $db->fetchByAssoc($result);
        $amountSum = $row["amount"];

        return is_numeric($amountSum) ? $amountSum : 0;
    }

    /**
     * retrieves the amount of opportunities with count less the closed won/lost stages
     *
     */
    public function getPipelineRevenue()
    {

        $db = DBManagerFactory::getInstance();
        $amountSum = 0;

        $user_id = $this->getArg('user_id');
        $timeperiod_id = $this->getArg('timeperiod_id');
        $excluded_sales_stages_won = $this->getArg('sales_stage_won');
        $excluded_sales_stages_lost = $this->getArg('sales_stage_lost');

        //Note: this will all change in sugar7 to the filter API
        //set up outer part of the query
        $query = "select sum(amount) as amount, sum(recordcount) as recordcount from(";
        
        //build up two subquery strings so we can unify the sales stage loops
        //all manager opps 
        $queryMgrOpps = "SELECT " .
                            "sum(o.amount*o.base_rate) AS amount, count(*) as recordcount " .
                        "FROM opportunities o " .
                        "INNER JOIN users u  " .
                            "ON o.assigned_user_id = u.id " .
                        "LEFT JOIN timeperiods t " .
                            "ON t.start_date_timestamp <= o.date_closed_timestamp " . 
                            "AND t.end_date_timestamp >= o.date_closed_timestamp " .
                        "WHERE " .
                            "t.id = " . $db->quoted($timeperiod_id) . " " . 
                            "AND o.deleted = 0 " .
                            "AND o.assigned_user_id = " . $db->quoted($user_id) . " ";
        
        //only committed rep opps
        $queryRepOpps = "SELECT " . 
                            "sum(w.likely_case * w.base_rate) AS amount, count(*) as recordcount " .
                        "FROM worksheet w " .
                        "INNER JOIN users u " .
                            "ON w.user_id = u.id " .
                                "AND u.reports_to_id = " . $db->quoted($user_id) . " " .
                        "INNER JOIN products p " .
                            "ON w.related_id = p.id " .
                        "INNER JOIN opportunities o " .
                            "ON p.opportunity_id = o.id " .
                        "WHERE " .
                            "w.timeperiod_id = " . $db->quoted($timeperiod_id) . " " . 
                            "AND w.deleted = 0 " .
                            "AND version = 1 " .
                            "AND forecast_type = 'Direct' ";
        
        //per requirements, exclude the sales stages won
        if (count($excluded_sales_stages_won)) {
            foreach ($excluded_sales_stages_won as $exclusion) {
                $queryMgrOpps .= "AND o.sales_stage != " . $db->quoted($exclusion) . " ";
                $queryRepOpps .= "AND o.sales_stage != " . $db->quoted($exclusion) . " ";
            }
        }

        //per the requirements, exclude the sales stages for closed lost
        if (count($excluded_sales_stages_lost)) {
            foreach ($excluded_sales_stages_lost as $exclusion) {
                $queryMgrOpps .= "AND o.sales_stage != " . $db->quoted($exclusion) . " ";
                $queryRepOpps .= "AND o.sales_stage != " . $db->quoted($exclusion) . " ";
            }
        }
        
        //Union the two together
        $query .= $queryMgrOpps . "union all " . $queryRepOpps;

        //finally, finish up the outer query
        $query .= ") sums";

        $result = $db->query($query);
        $row = $db->fetchByAssoc($result);
        $this->pipelineRevenue = is_numeric($row["amount"]) ? $row["amount"] : 0;
        $this->pipelineCount = is_numeric($row["recordcount"]) ? $row["recordcount"] : 0;
    }
}