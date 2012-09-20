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
    global $app_list_strings, $timedate, $current_language;
    $app_list_strings = return_app_list_strings_language($current_language);

    //Determine the default commit_stage based on the probability
    if (empty($focus->commit_stage) && $focus->probability !== '')
    {
        $admin = BeanFactory::getBean('Administration');
        $admin->retrieveSettings();

        //Retrieve Forecasts_category_ranges and json decode as an associative array
        $category_ranges = json_decode(html_entity_decode($admin->settings['Forecasts_category_ranges']), true);

        foreach($category_ranges as $key=>$entry)
        {
            if($focus->probability >= $entry['min'] && $focus->probability <= $entry['max'])
            {
               $focus->commit_stage = $key;
               break;
            }
        }
    }

    //Set the timeperiod_id value
    if ($timedate->check_matching_format($focus->date_closed, TimeDate::DB_DATE_FORMAT)) {
        $date_close_db = $focus->date_closed;
    } else {
        $date_close_db = $timedate->to_db_date($focus->date_closed);
    }

    // only do this if the date_closed changes or if no timeperiod_id is set
    if(empty($focus->timeperiod_id) || (isset($focus->fetched_row['date_closed']) && $focus->fetched_row['date_closed'] != $date_close_db)) {
        $timeperiod = TimePeriod::retrieveFromDate($date_close_db);

        if($timeperiod instanceof TimePeriod && !empty($timeperiod->id)) {
            $focus->timeperiod_id = $timeperiod->id;
        }
    }

    // if any of the case fields are NULL or an empty string set it to the amount from the main opportunity
    if(is_null($focus->best_case) || strval($focus->best_case) === "") {
        $focus->best_case = $focus->amount;
    }

    if(is_null($focus->worst_case) || strval($focus->worst_case) === "") {
        $focus->worst_case = $focus->amount;
    }

    // Bug49495: amount may be a calculated field
    $focus->updateCalculatedFields();
    //END SUGARCRM flav=pro ONLY

	//Store the base currency value
	if(isset($focus->amount) && !number_empty($focus->amount)){
        require_once 'include/SugarCurrency.php';
        $currency = new Currency();
		$currency->retrieve($focus->currency_id);
		$focus->amount_usdollar = SugarCurrency::convertAmountToBase($focus->amount,$currency->id);
    }

    //BEGIN SUGARCRM flav=pro ONLY
    //We create a related product entry for any new opportunity so that we may forecast on products
    if (empty($focus->id))
    {
        $focus->id = create_guid();
        $focus->new_with_id = true;

        $product = BeanFactory::getBean('Products');
        $product->name = $focus->name;
        $product->best_case = $focus->best_case;
        $product->likely_case = $focus->amount;
        $product->worst_case = $focus->worst_case;
        $product->assigned_user_id = $focus->assigned_user_id;
        $product->opportunity_id = $focus->id;
        $product->save();
    }
    //END SUGARCRM flav=pro ONLY
}
?>
