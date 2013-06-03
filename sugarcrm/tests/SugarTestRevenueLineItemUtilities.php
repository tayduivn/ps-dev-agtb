<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
 
require_once 'modules/RevenueLineItems/RevenueLineItem.php';

class SugarTestRevenueLineItemUtilities
{
    protected static $_createdRlis = array();

    private function __construct() {}

    public static function createRevenueLineItem($id = '') 
    {
        $time = mt_rand();
        $name = 'SugarRevenueLineItem';
        
        $rli = new RevenueLineItem();
        $rli->currency_id = '-99';
        $rli->name = $name . $time;
        $rli->tax_class = 'Taxable';
        $rli->cost_price = '100.00';
        $rli->list_price = '100.00';
        $rli->discount_price = '100.00';
        $rli->quantity = '100';

        //BEGIN SUGARCRM flav=ent ONLY
        $rli->best_case = '100.00';
        $rli->likely_case = '80.00';
        $rli->worst_case = '50.00';
        //END SUGARCRM flav=ent ONLY

        if(!empty($id))
        {
            $rli->new_with_id = true;
            $rli->id = $id;
        }
        $rli->save();
        self::$_createdRlis[] = $rli;
        return $rli;
    }

    public static function setCreatedRevenueLineItem($rli_ids) {
        foreach($rli_ids as $rli_id) {
            $rli = new RevenueLineItem();
            $rli->id = $rli_id;
            self::$_createdRlis[] = $rli;
        }
    }
    public static function removeAllCreatedRevenueLineItems() 
    {
        $db = DBManagerFactory::getInstance();
        $rli_ids = self::getCreatedRevenueLineItemIds();
        $db->query("DELETE FROM revenue_line_items WHERE id IN ('" . implode("', '", $rli_ids) . "')");
        $db->query("DELETE FROM revenue_line_items_audit WHERE parent_id IN ('" . implode("', '", $rli_ids) . "')");
        $db->query("DELETE FROM forecast_worksheets WHERE parent_type = 'RevenueLineItems' and parent_id IN ('" . implode("', '", $rli_ids) . "')");
    }
        
    public static function getCreatedRevenueLineItemIds() 
    {
        $product_ids = array();
        foreach (self::$_createdRlis as $rli) {
            $rli_ids[] = $rli->id;
        }
        return $rli_ids;
    }
}
?>