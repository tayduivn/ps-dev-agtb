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

class ForecastWorksheet extends SugarBean {

    var $id;
    var $worksheet_id;
    var $currency_id;
    var $base_rate;
    var $args;
    var $name;
    var $forecast;
    var $best_case;
    var $likely_case;
    var $worst_case;
    var $sales_stage;
    var $assigned_user_id;    
    var $object_name = 'ForecastWorksheet';
    var $module_dir = 'Forecasts';
    var $table_name = 'opportunities';
    var $disable_custom_fields = true;

    function __construct() {
        parent::__construct();
    }

    /**
     * Override save here to handle saving to the real tables.  Currently forecast is mapped to opportunities
     * and likely_case, worst_case and best_case go to both worksheets and opportunities.
     *
     *
     * @param bool $check_notify
     */
    function save($check_notify = false)
    {
    	$version = 1;
    	
    	if(isset($this->args["draft"]) && $this->args["draft"] == 1){
			$version = 0;
		}
		
		$worksheetID = $this->getWorksheetID($version);
    	
    	if($version != 0)
    	{
	        //Update the Opportunities bean
	        $opp = BeanFactory::getBean('Opportunities', $this->id);
	        $opp->forecast = ($this->forecast) ? 1 : 0;
	        $opp->probability = $this->probability;
	        $opp->best_case = $this->best_case;
	        $opp->sales_stage = $this->sales_stage;
	        $opp->commit_stage = $this->commit_stage;
	        $opp->save();
    	}
    	 
        //Update the Worksheet bean
		$worksheet  = BeanFactory::getBean('Worksheet', $worksheetID);
		$worksheet->timeperiod_id = $this->args["timeperiod_id"];
		$worksheet->user_id = $this->assigned_user_id;
		$worksheet->forecast = ($this->forecast) ? 1 : 0;
        $worksheet->best_case = $this->best_case;
        $worksheet->likely_case = $this->amount;
        $worksheet->op_probability = $this->probability;
        $worksheet->forecast_type = "Direct";
        $worksheet->related_forecast_type = "Product";
        $worksheet->related_id = $this->id;
        $worksheet->currency_id = $this->currency_id;
        $worksheet->base_rate = $this->base_rate;
        $worksheet->version = $version;
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
	 * Finds the id of the correct version row to update
	 * 
	 * @param int version
	 * @return uuid ID of row, null if not found.
	 */
	protected function getWorksheetID($version)
	{
		$id = null;
		$sql = "select id from worksheet " .
				"where timeperiod_id = '" . $this->args["timeperiod_id"] . "' " .
					"and user_id = '" . $this->args["current_user"] . "' " .
					"and version = '" . $version . "' " .
					"and related_id = '" . $this->args["id"] . "'";
		
		$result = $GLOBALS['db']->query($sql);
		while(($row=$GLOBALS['db']->fetchByAssoc($result))!=null){
			$id = $row['id'];
		}
		return $id;
	}
}

