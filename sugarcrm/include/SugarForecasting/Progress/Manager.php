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

require_once("include/SugarForecasting/Progress/AbstractProgress.php");
class SugarForecasting_Progress_Manager extends SugarForecasting_Progress_AbstractProgress
{
    /**
     * @var Opportunity
     */
    protected $opportunity;

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
    protected function getManagerProgress()
    {
        //create opportunity to use to build queries
        $this->opportunity = new Opportunity();

        //get data
		$progressData = array(
            "closed_amount"     => $this->getClosedAmount(),
            "opportunities"     => $this->getPipelineOpportunityCount(),
            "pipeline_revenue"  => $this->getPipelineRevenue()
		);

		return $progressData;
    }


    /**
     * retreives the number of opportunities set to be used in this forecast period, excludes only the closed stages
     *
     * @return mixed
     */
    protected function getPipelineOpportunityCount()
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
        $query = "SELECT sum(o.amount) AS amount FROM opportunities o INNER JOIN users u ";
        $query .= " ON o.assigned_user_id = u.id ";
        $query .= " left join timeperiods t ";
        $query .= " ON t.start_date_timestamp < o.date_closed_timestamp ";
        $query .= " AND t.end_date_timestamp >= o.date_closed_timestamp ";
        $query .= " WHERE t.id = " . $db->quoted($timeperiod_id);
        $query .= " AND o.deleted = 0 AND (u.reports_to_id = " . $db->quoted($user_id);
        $query .= " OR o.assigned_user_id = " . $db->quoted($user_id) . ")";

        //pre requirements, include only closed won opportunities
        if (!empty($sales_stage_won)) {
            $query .= " AND o.sales_stage in ( '";
            $query .= join("','", $sales_stage_won) . "')";
        }

        error_log($query);

        $result = $db->query($query);

        while ($row = $db->fetchByAssoc($result)) {
            $amountSum = $row["amount"];
        }

        return $amountSum;
    }

    /**
     * retrieves the amount of opportunities less the closed won/lost stages
     *
     * @return int
     */
    public function getPipelineRevenue()
    {

        $db = DBManagerFactory::getInstance();
        $amountSum = 0;

        $user_id = $this->getArg('user_id');
        $timeperiod_id = $this->getArg('timeperiod_id');
        $excluded_sales_stages_won = $this->getArg('sales_stage_won');
        $excluded_sales_stages_lost = $this->getArg('sales_stage_lost');

        //set user ids and timeperiods
        $query = "SELECT sum(o.amount) AS amount FROM opportunities o INNER JOIN users u ";
        $query .= " ON o.assigned_user_id = u.id";
        $query .= " left join timeperiods t ";
        $query .= " ON t.start_date_timestamp < o.date_closed_timestamp ";
        $query .= " AND t.end_date_timestamp >= o.date_closed_timestamp ";
        $query .= " WHERE t.id = " . $db->quoted($timeperiod_id);
        $query .= " AND o.deleted = 0 AND (u.reports_to_id = " . $db->quoted($user_id);
        $query .= " OR o.assigned_user_id = " . $db->quoted($user_id) . ")";


        //per requirements, exclude the sales stages won
        if (count($excluded_sales_stages_won)) {
            foreach ($excluded_sales_stages_won as $exclusion) {
                $query .= " AND o.sales_stage != " . $db->quoted($exclusion);
            }
        }

        //per the requirements, exclude the sales stages for closed lost
        if (count($excluded_sales_stages_lost)) {
            foreach ($excluded_sales_stages_lost as $exclusion) {
                $query .= " AND o.sales_stage != " . $db->quoted($exclusion);
            }
        }

        $result = $db->query($query);

        while ($row = $db->fetchByAssoc($result)) {
            $amountSum = $row["amount"];
        }

        return $amountSum;
    }
}