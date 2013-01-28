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

    public $id;
    public $worksheet_id;
    public $timeperiod_id;
    public $currency_id;
    public $base_rate;
    public $args;
    public $name;
    public $commit_stage;
    public $probability;
    public $best_case;
    public $likely_case;
    public $worst_case;
    public $sales_stage;
    public $product_id;
    public $assigned_user_id;
    public $draft;
    public $object_name = 'ForecastWorksheet';
    public $module_name = 'ForecastWorksheets';
    public $module_dir = 'Forecasts';
    public $table_name = 'opportunities';
    public $disable_custom_fields = true;

    /**
     * Override save here to handle saving to the real tables.  Currently forecast is mapped to opportunities
     * and likely_case, worst_case and best_case go to both worksheets and opportunities.
     *
     *
     * @param bool $check_notify        Should we send the notifications
     * @return string                   SugarGUID for the Worksheet that was modified or created
     */
    public function save($check_notify = false)
    {
    	$version = 1;
    	if(isset($this->draft) && $this->draft == 1){
			$version = 0;
		}

        //Update the Opportunities bean -- should update the product line item as well through SaveOverload.php
        $opp = BeanFactory::getBean('Opportunities', $this->id);
        $opp->probability = $this->probability;
        $opp->best_case = $this->best_case;
        $opp->amount = $this->likely_case;
        $opp->sales_stage = $this->sales_stage;
        $opp->commit_stage = $this->commit_stage;
        $opp->worst_case = $this->worst_case;
        $opp->commit_stage = $this->commit_stage;
        $opp->save($check_notify);
    	
    	if($version == 1)
    	{
	        //Update the Worksheet bean
			$worksheet  = BeanFactory::getBean('Worksheet', $this->worksheet_id);
			$worksheet->timeperiod_id = $this->timeperiod_id;
			$worksheet->user_id = $this->assigned_user_id;
	        $worksheet->best_case = $this->best_case;
	        $worksheet->likely_case = $this->likely_case;
	        $worksheet->worst_case = $this->worst_case;
	        $worksheet->op_probability = $this->probability;
	        $worksheet->commit_stage = $this->commit_stage;
	        $worksheet->forecast_type = "Direct";
	        $worksheet->related_forecast_type = "Product";
	        $worksheet->related_id = $this->product_id;
	        $worksheet->currency_id = $this->currency_id;
	        $worksheet->base_rate = $this->base_rate;
	        $worksheet->version = $version;
	        $worksheet->save($check_notify);
    	}

        //return $worksheet->id;
    }
    
    /**
     * Sets Worksheet args so that we save the supporting tables.
     * @param array $args Arguments passed to save method through PUT
     */
	public function setWorksheetArgs($args)
	{
        // save the args variable
		$this->args = $args;

        // loop though the args and assign them to the corresponding key on the object
        foreach($args as $arg_key => $arg) {
            $this->$arg_key = $arg;
        }
	}
	
    static public function reassignForecast($fromUserId, $toUserId)
    {
        global $current_user;

        $db = DBManagerFactory::getInstance();

        // reassign Opportunities
        $_object = new Opportunity();
        $_query = "update {$_object->table_name} set ".
            "assigned_user_id = '{$toUserId}', ".
            "date_modified = '".TimeDate::getInstance()->nowDb()."', ".
            "modified_user_id = '{$current_user->id}' ".
            "where {$_object->table_name}.deleted = 0 and {$_object->table_name}.assigned_user_id = '{$fromUserId}'";
        $res = $db->query($_query, true);
        $affected_rows = $db->getAffectedRowCount($res);

        // Products
        // reassign only products that have related opportunity - products created from opportunity::save()
        // other products will be reassigned if module Product is selected by user
        $_object = new Product();
        $_query = "update {$_object->table_name} set ".
            "assigned_user_id = '{$toUserId}', ".
            "date_modified = '".TimeDate::getInstance()->nowDb()."', ".
            "modified_user_id = '{$current_user->id}' ".
            "where {$_object->table_name}.deleted = 0 and {$_object->table_name}.assigned_user_id = '{$fromUserId}' and {$_object->table_name}.opportunity_id IS NOT NULL ";
        $db->query($_query, true);

        // delete Forecasts
        $_object = new Forecast();
        $_query = "update {$_object->table_name} set ".
            "deleted = 1, ".
            "date_modified = '".TimeDate::getInstance()->nowDb()."' ".
            "where {$_object->table_name}.deleted = 0 and {$_object->table_name}.user_id = '{$fromUserId}'";
        $db->query($_query, true);

        // delete Quotas
        $_object = new Quota();
        $_query = "update {$_object->table_name} set ".
            "deleted = 1, ".
            "date_modified = '".TimeDate::getInstance()->nowDb()."' ".
            "where {$_object->table_name}.deleted = 0 and {$_object->table_name}.user_id = '{$fromUserId}'";
        $db->query($_query, true);

        // clear reports_to for inactive users
        $objFromUser = new User();
        $objFromUser->retrieve($fromUserId);
        $fromUserReportsTo = !empty($objFromUser->reports_to_id) ? $objFromUser->reports_to_id : '';
        $objFromUser->reports_to_id = '';
        $objFromUser->save();

        if ( User::isManager($fromUserId) )
        {
            // setup report_to for user
            $objToUserId = new User();
            $objToUserId->retrieve($toUserId);
            $objToUserId->reports_to_id = $fromUserReportsTo;
            $objToUserId->save();

            // reassign users (reportees)
            $_object = new User();
            $_query = "update {$_object->table_name} set ".
                "reports_to_id = '{$toUserId}', ".
                "date_modified = '".TimeDate::getInstance()->nowDb()."', ".
                "modified_user_id = '{$current_user->id}' ".
                "where {$_object->table_name}.deleted = 0 and {$_object->table_name}.reports_to_id = '{$fromUserId}' ".
                "and {$_object->table_name}.id != '{$toUserId}'";
            $db->query($_query, true);
        }

        // Worksheets
        // reassign worksheets for products (opportunities)
        $_object = new Worksheet();
        $_query = "update {$_object->table_name} set ".
            "user_id = '{$toUserId}', ".
            "date_modified = '".TimeDate::getInstance()->nowDb()."', ".
            "modified_user_id = '{$current_user->id}' ".
            "where {$_object->table_name}.deleted = 0 and {$_object->table_name}.user_id = '{$fromUserId}' ";
        $db->query($_query, true);

        // delete worksheet where related_id is user id - rollups
        $_object = new Worksheet();
        $_query = "update {$_object->table_name} set ".
            "deleted = 1, ".
            "date_modified = '".TimeDate::getInstance()->nowDb()."', ".
            "modified_user_id = '{$current_user->id}' ".
            "where {$_object->table_name}.deleted = 0 ".
            "and {$_object->table_name}.forecast_type = 'Rollup' and {$_object->table_name}.related_forecast_type = 'Direct' ".
            "and {$_object->table_name}.related_id = '{$fromUserId}' ";
        $db->query($_query, true);

        //todo: forecast_tree

        return $affected_rows;
    }
}

