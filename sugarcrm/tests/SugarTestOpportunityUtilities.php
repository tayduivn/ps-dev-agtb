<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


$beanList = array();
$beanFiles = array();
require('include/modules.php');
$GLOBALS['beanList'] = $beanList;
$GLOBALS['beanFiles'] = $beanFiles;
require_once 'modules/Opportunities/Opportunity.php';

class SugarTestOpportunityUtilities
{
    private static $_createdOpps = array();

    private function __construct() {}

    public static function createOpportunity($id = '')
    {
        $time = mt_rand();
        $name = 'SugarOpp';
        $opp = new Opportunity();
        $opp->name = $name . $time;
        $opp->amount = '10000';
        $opp->date_closed = $GLOBALS['timedate']->to_display_date(gmdate('Y-m-d'));
        $opp->sales_stage = 'Prospecting';
        if(!empty($id))
        {
            $opp->new_with_id = true;
            $opp->id = $id;
        }
        $opp->save();
        self::$_createdOpps[] = $opp;
        return $opp;
    }

    public static function setCreatedOpp($opp_ids)
    {
        foreach($opp_ids as $opp_id)
        {
            $opp = new Opportunity();
            $opp->id = $opp_id;
            self::$_createdOpps[] = $opp;
        }
    }

    public static function removeAllCreatedOpps()
    {
        $opp_ids = self::getCreatedOppIds();
        $GLOBALS['db']->query('DELETE FROM opportunities WHERE id IN (\'' . implode("', '", $opp_ids) . '\')');
        $GLOBALS['db']->query('DELETE FROM opportunities_contacts WHERE opportunity_id IN (\'' . implode("', '", $opp_ids) . '\')');
    }

    public static function getCreatedOppIds()
    {
        $opp_ids = array();
        foreach (self::$_createdOpps as $opp)
        {
            $opp_ids[] = $opp->id;
        }
        return $opp_ids;
    }
}