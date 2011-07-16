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
 
require_once 'modules/Accounts/Account.php';

class SugarTestAccountUtilities
{
    private static $_createdAccounts = array();

    private function __construct() {}

    public static function createAccount($id = '') 
    {
        $time = mt_rand();
    	$name = 'SugarAccount';
    	$email1 = 'account@sugar.com';
    	$account = new Account();
        $account->name = $name . $time;
        $account->email1 = 'account@'. $time. 'sugar.com';
        if(!empty($id))
        {
            $account->new_with_id = true;
            $account->id = $id;
        }
        $account->save();
        $GLOBALS['db']->commit();
        self::$_createdAccounts[] = $account;
        return $account;
    }

    public static function setCreatedAccount($account_ids) {
    	foreach($account_ids as $account_id) {
    		$account = new Account();
    		$account->id = $account_id;
        	self::$_createdAccounts[] = $account;
    	} // foreach
    } // fn
    
    public static function removeAllCreatedAccounts() 
    {
        $account_ids = self::getCreatedAccountIds();
        $GLOBALS['db']->query('DELETE FROM accounts WHERE id IN (\'' . implode("', '", $account_ids) . '\')');
    }
        
    public static function getCreatedAccountIds() 
    {
        $account_ids = array();
        foreach (self::$_createdAccounts as $account) {
            $account_ids[] = $account->id;
        }
        return $account_ids;
    }
}
?>