<?php
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
 
require_once 'modules/Leads/Lead.php';

class SugarTestLeadUtilities
{
    private static $_createdLeads = array();

    private function __construct() {}

    public static function createLead($id = '') 
    {
        $time = mt_rand();
    	$first_name = 'SugarLeadFirst';
    	$last_name = 'SugarLeadLast';
    	$email1 = 'lead@sugar.com';
    	$lead = new Lead();
        $lead->first_name = $first_name . $time;
        $lead->last_name = $last_name ;
        $lead->email1 = 'lead@'. $time. 'sugar.com';
        if(!empty($id))
        {
            $lead->new_with_id = true;
            $lead->id = $id;
        }
        $lead->save();
        self::$_createdLeads[] = $lead;
        return $lead;
    }

    public static function setCreatedLead($lead_ids) {
    	foreach($lead_ids as $lead_id) {
    		$lead = new Lead();
    		$lead->id = $lead_id;
        	self::$_createdLeads[] = $lead;
    	} // foreach
    } // fn
    
    public static function removeAllCreatedLeads() 
    {
        $lead_ids = self::getCreatedLeadIds();
        $GLOBALS['db']->query('DELETE FROM leads WHERE id IN (\'' . implode("', '", $lead_ids) . '\')');
    }
    
    public static function removeCreatedLeadsUsersRelationships(){
    	$lead_ids = self::getCreatedLeadIds();
        $GLOBALS['db']->query('DELETE FROM leads_users WHERE lead_id IN (\'' . implode("', '", $lead_ids) . '\')');
    }
    
    public static function getCreatedLeadIds() 
    {
        $lead_ids = array();
        foreach (self::$_createdLeads as $lead) {
            $lead_ids[] = $lead->id;
        }
        return $lead_ids;
    }
}
?>