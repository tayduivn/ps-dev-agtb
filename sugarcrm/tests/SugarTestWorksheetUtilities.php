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
require_once 'modules/Forecasts/Worksheet.php';

class SugarTestWorksheetUtilities
{
    private static $_createdWorksheets = array();

    private function __construct() {}

    public static function createWorksheet($id = '')
    {
        $time = mt_rand();
        $name = 'SugarWorksheet';
        $worksheet = new Worksheet();
        $worksheet->name = $name . $time;

        if(!empty($id))
        {
            $worksheet->new_with_id = true;
            $worksheet->id = $id;
        }
        $worksheet->save();
        self::$_createdWorksheets[] = $worksheet;
        return $worksheet;
    }

    public static function setCreatedWorksheet($worksheet_ids)
    {
        foreach($worksheet_ids as $worksheet_id)
        {
            $worksheet = new Worksheet();
            $worksheet->id = $worksheet_id;
            self::$_createdWorksheets[] = $worksheet;
        }
    }

    public static function removeAllCreatedWorksheets()
    {
        $worksheet_ids = self::getCreatedWorksheetIds();
        //clean up any worksheets and draft versions as well.  The drafts were made by code, not the tests,
        //so we have to do some shenanigans to find them.
        $GLOBALS['db']->query('delete from worksheet where user_id in(select user_id from (select user_id FROM worksheet WHERE id IN (\'' . implode("', '", $worksheet_ids) . '\')) temp)');
    }

    public static function getCreatedWorksheetIds()
    {
        $worksheet_ids = array();
        foreach (self::$_createdWorksheets as $worksheet)
        {
            $worksheet_ids[] = $worksheet->id;
        }
        return $worksheet_ids;
    }
}