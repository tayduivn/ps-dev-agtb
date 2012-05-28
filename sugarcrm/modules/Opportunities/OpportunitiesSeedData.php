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
 * OpportunitiesSeedData.php
 *
 * This is a class used for creating OpportunitiesSeedData.  We moved this code out from install/populateSeedData.php so
 * that we may better control and test creating default Opportunities.
 *
 */

class OpportunitiesSeedData {

/**
 * populateSeedData
 *
 * This is a static function to create Opportunities.
 *
 * @static
 * @param $records Integer value indicating the number of Opportunities to create
 * @param $app_list_strings Array of application language strings
 * @param $accounts Array of Account instances to randomly build data against
 //BEGIN SUGARCRM flav=pro ONLY
 * @param $timeperiods Array of Timeperiods to create timeperiod seed data off of
 * @param $products Array of Product instances to randomly build data against
 * @param $users Array of User instances to randomly build data against
 //END SUGARCRM flav=pro ONLY
 * @return array Array of Opportunities created
 */
public static function populateSeedData($records, $app_list_strings, $accounts
//BEGIN SUGARCRM flav=pro ONLY
    ,$products, $users
//END SUGARCRM flav=pro ONLY
)
{
    if(empty($accounts) || empty($app_list_strings) || (!is_int($records) || $records < 1)
//BEGIN SUGARCRM flav=pro ONLY
       || empty($products) || empty($users)
//END SUGARCRM flav=pro ONLY

    )
    {
        return array();
    }

    $timedate = TimeDate::getInstance();

    //BEGIN SUGARCRM flav=ent ONLY
    $opportunityLine = new OpportunityLine();
    //END SUGARCRM flav=ent ONLY

    while($records-- > 0)
    {
        $key = array_rand($accounts);
        $account = $accounts[$key];

        //Create new opportunities
        $opp = new Opportunity();
        //BEGIN SUGARCRM flav=pro ONLY
        $opp->team_id = $account->team_id;
        $opp->team_set_id = $account->team_set_id;
        $worst_case = array("2500", "7500", "15000", "25000");
        $likely_case = array("5000", "10000", "20000", "50000");
        $best_case = array("7500", "12500", "25000", "60000");
        $key = array_rand($best_case);
        $opp->worst_case = $worst_case[$key];
        $opp->likely_case = $likely_case[$key];
        $opp->best_case = $best_case[$key];
        //END SUGARCRM flav=pro ONLY
        $opp->assigned_user_id = $account->assigned_user_id;
        $opp->assigned_user_name = $account->assigned_user_name;
        $opp->name = substr($account->name." - 1000 units", 0, 50);
        $opp->date_closed = create_date();
        $opp->lead_source = array_rand($app_list_strings['lead_source_dom']);
        $opp->sales_stage = array_rand($app_list_strings['sales_stage_dom']);
        // If the deal is already one, make the date closed occur in the past.
        if($opp->sales_stage == "Closed Won" || $opp->sales_stage == "Closed Lost")
        {
            $opp->date_closed = create_past_date();
        }
        $opp->opportunity_type = array_rand($app_list_strings['opportunity_type_dom']);
        $amount = array("10000", "25000", "50000", "75000");
        $key = array_rand($amount);
        $opp->amount = $amount[$key];
        $probability = array("10", "70", "40", "60");
        $key = array_rand($probability);
        $opp->probability = $probability[$key];
        $opp->save();
        // Create a linking table entry to assign an account to the opportunity.
        $opp->set_relationship('accounts_opportunities', array('opportunity_id'=>$opp->id ,'account_id'=> $account->id), false);


        //BEGIN SUGARCRM flav=ent ONLY
        $line_item_count = mt_rand(0,2);

        while($line_item_count-- >= 0)
        {
            //Get a random product_line_data entry
            $key = array_rand($products);
            $product = $products[$key];

            //Get a random user entry
            $key = array_rand($users);
            $user = $users[$key];

            $opportunityLine->id = null;
            $opportunityLine->product_id = $product->id;
            $opportunityLine->opportunity_id = $opp->id;
            $opportunityLine->best_case = $opp->amount;
            $opportunityLine->likely_case = $opp->amount * .85;
            $opportunityLine->worst_case = $opp->amount * .7;
            $opportunityLine->created_by = $opp->assigned_user_id;
            $opportunityLine->modified_user_id = $opp->assigned_user_id;
            $opportunityLine->date_entered = $timedate->asDb($timedate->getNow());
            $opportunityLine->date_modified = $opportunityLine->date_entered;
            $opportunityLine->description = $product->name;
            $opportunityLine->expert_id = $user['id'];
            $opportunityLine->save();
        }
        //END SUGARCRM flav=ent ONLY

        $opportunity_ids[] = $opp->id;
    }

    return $opportunity_ids;
}

}