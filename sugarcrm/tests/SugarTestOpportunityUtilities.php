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
require_once 'modules/Opportunities/Opportunity.php';

class SugarTestOpportunityUtilities
{
    
    private static $_createdOpportunities = array();
    
    private static $_createdAccount = null;
    
    private function __construct()
    {
    }
    
    private function _createAccount($time)
    {
        if (self::$_createdAccount === null) 
        {
            $name = 'SugarOpportunityAccount';
            $account = new Account();
            $account->name = $name . $time;
            $account->email1 = 'account@' . $time . 'sugar.com';
            $account->save();
            
            $GLOBALS['db']->commit();
            self::$_createdAccount = $account;
        }

        return self::$_createdAccount;
    }
    
    private function _createOpportunity($id, $time, $account)
    {
        global $timedate;
        $name = 'SugarOpportunity';

        $opportunity = new Opportunity();
            
        if (!empty($id)) 
        {
            $opportunity->new_with_id = true;
            $opportunity->id = $id;
        }

        $opportunity->name         = $name . $time;
        $opportunity->amount       = 10000;
        $opportunity->account_id   = $account->id;
        $opportunity->account_name = $account->name;
        $opportunity->date_closed  = $timedate->to_display_date_time(gmdate("Y-m-d H:i:s"));
        $opportunity->save();

        $GLOBALS['db']->commit();
        
        self::$_createdOpportunities[] = $opportunity;
        
        return $opportunity;
    }
    
    public static function createOpportunity($id = '')
    {
        $time        = mt_rand();
        $account     = self::_createAccount($time);
        $opportunity = self::_createOpportunity($id, $time, $account);
        
        return $opportunity;
    }
    
    public static function setCreatedOpportunity($opportunity_ids)
    {
        foreach ($opportunity_ids as $opportunity_id) 
        {
            $opportunity = new Opportunity();
            $opportunity->id = $opportunity_id;
            self::$_createdOpportunities[] = $opportunity;
        }
    }
    
    public static function removeAllCreatedOpportunities()
    {
        $opportunity_ids = self::getCreatedOpportunityIds();
        
        if (!empty($opportunity_ids))
        {
            $GLOBALS['db']->query('DELETE FROM opportunities WHERE id IN (\'' . implode("', '", $opportunity_ids) . '\')');
            $GLOBALS['db']->query('DELETE FROM opportunities_contacts WHERE opportunity_id IN (\'' . implode("', '", $opportunity_ids) . '\')');
        }

        if (self::$_createdAccount !== null && self::$_createdAccount->id)
        {
            $GLOBALS['db']->query('DELETE FROM accounts WHERE id = \'' . self::$_createdAccount->id . '\'');
        }
    }
    
    public static function getCreatedOpportunityIds()
    {
        $opportunity_ids = array();
        
        foreach (self::$_createdOpportunities as $opportunity) 
        {
            $opportunity_ids[] = $opportunity->id;
        }
        
        return $opportunity_ids;
    }
}
?>