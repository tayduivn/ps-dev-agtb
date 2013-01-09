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

require_once 'modules/Filters/Filters.php';

class SugarTestFilterUtilities
{
    private static $_createdFilters = array();

    private function __construct() {}

    public static function createFilter($id = '')
    {
        $time = mt_rand();
        $name = 'SugarFilter';
        $filter = new Filter();
        $filter->name = $name . $time;
        if(!empty($id))
        {
            $filter->new_with_id = true;
            $filter->id = $id;
        }
        $filter->save();
        $GLOBALS['db']->commit();
        self::$_createdFilters[] = $filter;
        return $filter;
    }

    public static function setCreatedFilter($filter_ids) {
        foreach($filter_ids as $filter_id) {
            $filter = new Filter();
            $filter->id = $filter_id;
            self::$_createdFilters[] = $filter;
        } // foreach
    } // fn

    public static function removeAllCreatedFilters()
    {
        $filter_ids = self::getCreatedFilterIds();
        $GLOBALS['db']->query('DELETE FROM filters WHERE id IN (\'' . implode("', '", $filter_ids) . '\')');
    }

    public static function getCreatedFilterIds()
    {
        $filter_ids = array();
        foreach (self::$_createdFilters as $filter) {
            $filter_ids[] = $filter->id;
        }
        return $filter_ids;
    }
}
?>