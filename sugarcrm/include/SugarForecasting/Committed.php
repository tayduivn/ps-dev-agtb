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
            $row['date_entered'] = $this->convertDateTimeToISO($row['date_entered']);
            $row['date_modified'] = $this->convertDateTimeToISO($row['date_modified']);
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
        
        $args['opp_count'] = (!isset($args['opp_count'])) ? 0 : $args['opp_count'];

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

        if ($args['amount'] != 0 && $args['opp_count'] != 0) {
            $forecast->opp_weigh_value = $args['amount'] / $args['opp_count'];
        }
        $forecast->save();

        //If there are any new worksheet entries that need created, do that here.
        foreach($args["worksheetData"]["new"] as $sheet)
        {
            //Update the Worksheet bean
            $worksheet  = BeanFactory::getBean("Worksheet");
            $worksheet->timeperiod_id = $args["timeperiod_id"];
            $worksheet->user_id = $current_user->id;
            $worksheet->best_case = $sheet["best_case"];
            $worksheet->likely_case = $sheet["likely_case"];
            $worksheet->worst_case = $sheet["worst_case"];
            $worksheet->op_probability = $sheet["probability"];
            $worksheet->commit_stage = $sheet["commit_stage"];
            $worksheet->forecast_type = "Direct";
            $worksheet->related_forecast_type = "Product";
            $worksheet->related_id = $sheet["product_id"];
            $worksheet->currency_id = $args["currency_id"];
            $worksheet->base_rate = $args["base_rate"];
            $worksheet->version = 1;
            $worksheet->save();
        }
        
        //Now we need to update any existing sheets using an ANSI standard update join
        //that should work across all DBs
        $worksheetIds = array();
        foreach($args["worksheetData"]["current"] as $sheet)
        {
            $worksheetIds[] = $sheet["worksheet_id"];
        }
        
        if(count($worksheetIds) > 0)
        {
            $sql = "update worksheet " .
                       "set best_case =     (" .
                                               "select p.best_case " .
                                               "from products p " .
                                               "where p.id = related_id" .
                                           "), " .
                           "likely_case = (" .
                                               "select p.likely_case " .
                                               "from products p " .
                                               "where p.id = related_id" .
                                           "), " .
                           "worst_case = (" .
                                               "select p.worst_case " .
                                               "from products p " .
                                               "where p.id = related_id" .
                                           "), " .
                           "op_probability = (" .
                                                   "select p.probability " .
                                                   "from products p " .
                                                   "where p.id = related_id" .
                                               "), " .
                           "commit_stage = (" .
                                               "select p.commit_stage " .
                                               "from products p " .
                                               "where p.id = related_id" .
                                             "), " .
                           "version = 1 " .
                   "where exists (" .
                                   "select * " .
                                   "from products p " .
                                   "where p.id = related_id" .
                                 ") " .
                "and id in ('" . implode("', '", $worksheetIds) . "')";
                                    
            $db->query($sql, true);                      
        }
        

        $timedate = TimeDate::getInstance();
        $forecast->date_entered = $this->convertDateTimeToISO($forecast->date_entered);
        $forecast->date_modified = $this->convertDateTimeToISO($forecast->date_modified);

        return $forecast->toArray(true);
    }
    
    /**
     * mgrNeedsCommitted
     * This checks to see if the Manager Worksheet committed date is older than the rep
     * worksheet committed date. If so, it retuns true so the UI can display a warning to the user.
     * @retun array Array that contains true/false
     */
    public function mgrNeedsCommitted()
    {
        global $current_user;
        if($current_user->id != $this->args["user_id"])
        {
            return array("needsCommitted"=>false);
        }
        $mgrCommitDate = "";
        $repCommitDate = "";
        $returnValue = false;
        
        $db = DBManagerFactory::getInstance();
        $queryData = array($this->args["timeperiod_id"], $this->args["user_id"], "Rollup");
        
        //Get most recent committed date on the manager sheet
        $sql = "select date_entered from forecasts " .
                "where timeperiod_id = '?' " .
                    "and user_id = '?' " .
                    "and forecast_type = '?' " .
                "order by date_entered desc";
        $sql = $db->limitQuerySql($sql, 0, 1);
        $result = $db->pQuery($sql, $queryData);
                
        if(($row = $db->fetchByAssoc($result)) != null)
        { 
            $mgrCommitDate = strtotime($row["date_entered"]);
        }
        
        //Get most recent committed date on the rep sheet -- simple modification to data passed to query
        $queryData[2] = "Direct";
        $result = $db->pQuery($sql, $queryData);
                
        if(($row = $db->fetchByAssoc($result)) != null)
        {                       
            $repCommitDate = strtotime($row["date_entered"]);
        }
        
        //Now, compare dates to see if the manager date is null, or older than the rep date.
        if($mgrCommitDate == "" && $repCommitDate != ""){
            $returnValue = true;
        }
        else if($repCommitDate == "" || ($repCommitDate == "" && $mgrCommitDate == "")){
            $returnValue = false;
        }
        else if($mgrCommitDate < $repCommitDate){
            $returnValue = true;
        }
        
        return array("needsCommitted"=>$returnValue);       
    }
}