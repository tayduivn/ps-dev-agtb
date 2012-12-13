<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 * Stores Temporary Forecasts Information for a given user
 */
class Worksheet extends SugarBean {

    public $id;
    public $user_id;
    public $timeperiod_id;
    public $forecast_type;
    public $related_id;
    public $related_forecast_type;
    public $currency_id;
    public $base_rate;
    public $best_case;
    public $likely_case;
    public $worst_case;
    public $date_modified;
    public $modified_user_id;
    public $deleted;
    public $commit_stage;
    public $op_probability;
    public $quota;
    public $version;

    public $table_name = "worksheet";

    public $object_name = "Worksheet";
    public $module_name = 'Worksheet';
    public $disable_custom_fields = true;

    // This is used to retrieve related fields from form posts.
    public $additional_column_fields = Array('');

    public $new_schema = true;
    public $module_dir = 'Forecasts';
    
    public function __construct() {
        parent::__construct();
        $this->disable_row_level_security = true;
    }

    /**
     * Save method
     * 
     * @param bool $check_notify
     * @return String|void
     */
    public function save($check_notify = false){
        if(empty($this->id) || $this->new_with_id == true) {
        	$currency = SugarCurrency::getBaseCurrency();
            $this->currency_id = $currency->id;
            $this->base_rate = $currency->conversion_rate;
        }
        
        return parent::save($check_notify);
    }

    /**
     * Get the Summary text For this bean.
     * 
     * @return string
     */
    public function get_summary_text() {
        return $this->id;
    }

    /**
     * Not sure what what method does as it's not used anywhere in the code.
     * 
     * @deprecated
     * @return mixed
     */
    public function is_authenticated()
    {
        return $this->authenticated;
    }

}
