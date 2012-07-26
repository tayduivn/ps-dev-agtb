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

function perform_save(&$focus){
    //BEGIN SUGARCRM flav=pro ONLY
    //if forecast value equals to -1, set it to 0 or 1 based on probability
    global $sugar_config, $app_list_strings, $timedate;
    if ( $focus->forecast == -1 )
    {
        $focus->forecast = ($focus->probability >= $sugar_config['forecast_committed_probability']) ? 1 : 0;
    }

    //if commit_stage isn't set, set it based on the probability
    if (empty($focus->commit_stage) && isset($focus->probability))
    {
        $commit_stage_arr = $app_list_strings['commit_stage_dom'];
        ksort($commit_stage_arr);
        //the keys of this array are upper limit of probability for each stage
        foreach($commit_stage_arr as $key => $value)
        {
            $focus->commit_stage = $key;
            if($focus->probability < $key)
            {
                break;
            }
        }
    }

    //Bug #54533 - The opportunities with "Closed Lost" stage should not be included in the forecast
    //if sales_stage was changed to "Closed Lost" set forecast to 0 and commit_stage to "Omit"
    if ($focus->sales_stage == "Closed Lost")
    {
        $focus->forecast = 0;
        $commit_stage_arr = $app_list_strings['commit_stage_dom'];
        $focus->commit_stage = min(array_keys($commit_stage_arr));
    }

    //Set the timeperiod_id value
    if ($timedate->check_matching_format($focus->date_closed, $timedate::DB_DATE_FORMAT))
    {
        $date_close_db = $focus->date_closed;
    }
    else
    {
        $date_close_db = $timedate->to_db_date($focus->date_closed);
    }

    // we only do this if no timeperiod_id is set.  This happens by default form the UI but when running a UnitTest,
    // we don't want to override any set timeperiod_id
    if(empty($focus->timeperiod_id))
    {
        $timeperiod = $focus->db->getOne("SELECT id FROM timeperiods WHERE start_date <= '{$date_close_db}' AND end_date >= '{$date_close_db}' AND is_fiscal_year = 0 AND deleted = 0");
        if (!empty($timeperiod))
        {
            $focus->timeperiod_id = $timeperiod;
        }
    }

    // Bug49495: amount may be a calculated field
    $focus->updateCalculatedFields();
    //END SUGARCRM flav=pro ONLY
	//US DOLLAR
	if(isset($focus->amount) && !number_empty($focus->amount)){
		$currency = new Currency();
		$currency->retrieve($focus->currency_id);
		$focus->amount_usdollar = $currency->convertToDollar($focus->amount);
	}	
}
?>
