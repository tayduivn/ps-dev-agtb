<?php
//FILE SUGARCRM flav=pro ONLY
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


global $sugar_config, $db, $timedate;
$large_scale_test = empty($sugar_config['large_scale_test']) ? false : $sugar_config['large_scale_test'];
$count = $large_scale_test ? 500 : 50;

//Retrieve a sample of the product_template entries to use and store this in product_line_data array
$result = $db->limitQuery("select id, list_price, discount_price, discount_usdollar, currency, currency_id, tax_class from product_templates", 0, $count);
$product_line_data = array();
while(($row = $db->fetchByAssoc($result)))
{
    $product_line_data[] = $row;
}

$result = $db->limitQuery("SELECT id, name FROM opportunities", 0, $count);
$opportunityLineBundle = new OpportunityLineBundle();
$opportunityLine = new OpportunityLine();

while(($row = $db->fetchByAssoc($result)))
{
    //Get a random product_line_data entry
    $key = array_rand($product_line_data);
    $product = $product_line_data[$key];

    $opportunityLineBundle->id = null;
    $opportunityLineBundle->name = $row['name'];
    $opportunityLineBundle->created_by = $opp->assigned_user_id;
    $opportunityLineBundle->modified_user_id = $opp->assigned_user_id;
    $opportunityLineBundle->date_created = $timedate->asDb($timedate->getNow());
    $opportunityLineBundle->date_entered = $timedate->asDb($timedate->getNow());
    $opportunityLineBundle->deleted = 0;
    $opportunityLineBundle->save();

    $opportunityLine->id = null;
    $opportunityLine->product_id = $product['id'];
    $opportunityLine->opportunity_id = $row['id'];
    $opportunityLine->quantity = 1;
    $opportunityLine->price = $product['list_price'];
    $opportunityLine->discount_price = $product['discount_price'];
    $opportunityLine->discount_usdollar = $product['discount_usdollar'];
    $opportunityLine->best_case = $opportunityLine->price;
    $opportunityLine->likely_case = $opportunityLine->discount_price;
    $opportunityLine->worst_case = $opportunityLine->discount_price * .75;
    $opportunityLine->currency = $product['currency'];
    $opportunityLine->currency_id = $product['currency_id'];
    $opportunityLine->tax_class = $product['tax_class'];
    $opportunityLine->deleted = 0;
    $opportunityLine->save();

    $opportunityLineBundle->set_opportunitylinebundle_opportunity_relationship($row['id'], $opportunityLineBundle->id, 1);
    $opportunityLineBundle->set_opportunitylinebundle_opportunityline_relationship($opportunityLine->id, 1, $opportunityLineBundle->id);

}
