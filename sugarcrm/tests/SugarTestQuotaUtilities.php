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

$beanList = array();
$beanFiles = array();
require('include/modules.php');
$GLOBALS['beanList'] = $beanList;
$GLOBALS['beanFiles'] = $beanFiles;
require_once 'modules/Quotas/Quota.php';

class SugarTestQuotaUtilities
{
    private static $_createdQuotas = array();

    private function __construct() {}

    public static function createQuota($amount=500, $id = '')
    {
        $quota = new Quota();
        $quota->amount = $amount;
        $quota->currency_id = -99;
        $quota->committed = 1;
        if(!empty($id))
        {
            $quota->new_with_id = true;
            $quota->id = $id;
        }
        $quota->save();
        self::$_createdQuotas[] = $quota;
        return $quota;
    }

    public static function setCreatedQuota($quota_ids) {
    	foreach($quota_ids as $quota_id) {
    		$quota = new Quota();
    		$quota->id = $quota_id;
        	self::$_createdQuotas[] = $quota;
    	} // foreach
    } // fn

    public static function removeAllCreatedQuotas()
    {
        $quota_ids = self::getCreatedQuotaIds();
        $GLOBALS['db']->query('DELETE FROM quotes WHERE id IN (\'' . implode("', '", $quota_ids) . '\')');
    }

    public static function getCreatedQuotaIds()
    {
        $quota_ids = array();
        foreach (self::$_createdQuotas as $quota) {
            $quota_ids[] = $quota->id;
        }
        return $quota_ids;
    }
}
?>