<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

class ForecastManagerWorksheet extends SugarBean
{
	var $args;
    var $id;
    var $name;
    var $forecast;
    var $best_case;
    var $likely_case;
    var $worst_case;
    var $object_name = 'ForecastManagerWorksheet';
    var $module_dir = 'Forecasts';
    var $table_name = 'forecasts';
    var $disable_custom_fields = true;

    function __construct() {
        parent::__construct();
    }

    public function save($check_notify = false)
    {
    	//skip this because nothing in the click to edit makes the worksheet modify the forecasts.
    	//leaving this here just in case we need it in the future.
    	//save forecast
    	/*if(isset($this->id)){
	    	$forecast = new Forecast();
			$forecast->retrieve($this->args["forecast_id"]);
			$forecast->best_case = $this->best_case;
			$forecast->likely_case = $this->likely_case;
			$forecast->forecast = ($this->forecast) ? 1 : 0;
			$forecast->save();
    	}*/

		//save quota
        /* @var $quota Quota */
        $quota = BeanFactory::getBean('Quotas', $this->args['quota_id']);
		$quota->timeperiod_id = $this->args["timeperiod_id"];
		$quota->user_id = $this->args["user_id"];
        $quota->committed = 1;
		if($this->args["user_id"] == $this->args["current_user"]) {
			$quota->quota_type = 'Direct';
		} else {
			$quota->quota_type = 'Rollup';
		}

		$quota->amount = $this->args["quota"];

		$quota->save();

		//recalc manager quota if necessary
		$this->recalcQuotas();

		//save worksheet
		$worksheet  = new Worksheet();
		$worksheet->retrieve($this->args["worksheet_id"]);
		$worksheet->timeperiod_id = $this->args["timeperiod_id"];
		$worksheet->user_id = $this->args["current_user"];
		$worksheet->forecast = ($this->args["forecast"]) ? 1 : 0;
        $worksheet->best_case = $this->args["best_adjusted"];
        $worksheet->likely_case = $this->args["likely_adjusted"];
        $worksheet->forecast_type = ($this->args["user_id"] == $GLOBALS['current_user']->id) ? "Direct" : "Rollup";
        $worksheet->related_forecast_type = $worksheet->forecast_type;
        $worksheet->worst_case = $this->worst_case;
        $worksheet->related_id = $this->args["user_id"];
        $worksheet->save();
    }

    /**
     * Sets Worksheet args so that we save the supporting tables.
     * @param array $args Arguments passed to save method through PUT
     */
	public function setWorksheetArgs($args)
	{
		$this->args = $args;
	}

	/**
	 * Gets a sum of the passed in user's reportees quotas for a specific timeperiod
	 *
	 * @param string $userId The userID for which you want a reportee quota sum.
	 * @return int Sum of quota amounts.
	 */
	protected function getQuotaSum($userId)
	{
		$sql = "SELECT sum(q.amount) amount " .
				"FROM `quotas` q " .
				"INNER JOIN users u ON u.reports_to_id = '" . $userId . "' " .
				"AND q.user_id = u.id " .
				"AND q.timeperiod_id = '" . $this->args["timeperiod_id"] . "' " .
				"AND q.quota_type = 'Rollup'";
		$amount = 0;

		$result = $GLOBALS['db']->query($sql);
		while(($row=$GLOBALS['db']->fetchByAssoc($result))!=null){
			$amount = $row['amount'];
		}

		return $amount;
	}

	/**
	 * Gets the passed in user's comitted quota value and direct quota ID
	 *
	 * @param string userId User id to query for
	 * @return array id, Quota value
	 */
	protected function getManagerQuota($userId)
	{
		/*
		 * This info is in two rows, and either of them might not exist.  The union
		 * is here to make sure data is returned if one or the other exists.  This statement
		 * lets us grab both bits with one call to the db rather than two separate smaller
		 * calls.
		 *
		 * We are looking for the ID of the quota where quota_type = Direct
		 * and the AMOUNT of the quota where quota_type = Rollup
		 */
		$sql = "SELECT q1.amount, q2.id FROM quotas q1 " .
				"left outer join quotas q2 " .
					"on q1.user_id = q2.user_id " .
					"and q1.timeperiod_id = q2.timeperiod_id " .
					"and q2.quota_type = 'Direct' " .
				"where q1.user_id = '" . $userId . "' " .
					"and q1.timeperiod_id = '" . $this->args["timeperiod_id"] . "'" .
					"and q1.quota_type = 'Rollup' " .
				"union all " .
				"SELECT q2.amount, q1.id FROM quotas q1 " .
				"left outer join quotas q2 " .
					"on q1.user_id = q2.user_id " .
					"and q1.timeperiod_id = q2.timeperiod_id " .
					"and q2.quota_type = 'Rollup' " .
				"where q1.user_id = '" . $userId . "' " .
					"and q1.timeperiod_id = '" . $this->args["timeperiod_id"] . "'" .
					"and q1.quota_type = 'Direct'";

		$quota = array();

		$result = $GLOBALS["db"]->query($sql);
		while(($row=$GLOBALS["db"]->fetchByAssoc($result))!=null){
			$quota["amount"] = $row["amount"];
			$quota["id"] = $row["id"];
		}

		return $quota;
	}

	/**
	 * Recalculates quotas based on committed values and reportees' quota values
	 */
	 protected function recalcQuotas()
	 {

	 	//don't recalc if we are editing the manager row
	 	if($this->args["user_id"] != $this->args["current_user"]){
			//Recalc Manager direct
			$this->recalcUserQuota($this->args["current_user"]);

			//Recalc reportee direct
			$this->recalcUserQuota($this->args["user_id"]);
	 	}
	 }

	 /**
	  * Recalculates a specific user's direct quota
	  *
	  * @param string $userId User Id of quota that needs recalculated.
	  */
	  protected function recalcUserQuota($userId)
	  {
	  	$reporteeTotal = $this->getQuotaSum($userId);
	 	$managerQuota = $this->getManagerQuota($userId);
	 	$managerAmount = ($managerQuota["amount"]) ? $managerQuota["amount"] : 0;
	 	$newTotal = $managerAmount - $reporteeTotal;
	 	if($newTotal < 0){
	 		$newTotal = 0;
	 	}

	 	//save Manager quota
	 	if(isset($managerQuota["id"])){
			$quota = BeanFactory::getBean('Quotas', $managerQuota['id']);
			$quota->amount = $newTotal;
			$quota->save();
	 	}
	  }


}

