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

require_once 'modules/ActivityStream/Activities/Activity.php';

class SugarTestActivityUtilities
{
    private static $_createdActivities = array();

    public static function createUnsavedActivity($new_id = '')
    {
        $time = mt_rand();
        $data = array('value' => 'SugarActivity' . $time);
        $activity = new Activity();
        $activity->data = $data;
        if (!empty($new_id)) {
            $activity->new_with_id = true;
            $activity->id = $new_id;
        }
        return $activity;
    }

    public static function createActivity($new_id = '')
    {
        $activity = self::createUnsavedActivity($new_id);
        $activity->save();
        $GLOBALS['db']->commit();
        self::$_createdActivities[] = $activity;
        return $activity;
    }

    public static function setCreatedActivity($activity_ids)
    {
        foreach ($activity_ids as $activity_id) {
            $activity = new Activity();
            $activity->id = $activity_id;
            self::$_createdActivities[] = $activity;
        }
    }

    public static function removeAllCreatedActivities()
    {
        $activity_ids = self::getCreatedActivityIds();
        $GLOBALS['db']->query('DELETE FROM activities_users WHERE activity_id IN (\'' . implode("', '", $activity_ids) . '\')');
        $GLOBALS['db']->query('DELETE FROM activities WHERE id IN (\'' . implode("', '", $activity_ids) . '\')');
    }

    public static function removeActivities(SugarBean $record)
    {
        $sql = 'DELETE FROM activities WHERE ';
        $sql .= 'activities.parent_module = "' . $record->module_name . '" ';
        if ($record->id) {
            $sql .= 'AND activities.parent_id = "' . $record->id . '"';
        }
        $GLOBALS['db']->query($sql);
    }

    public static function getCreatedActivityIds()
    {
        $activity_ids = array();
        foreach (self::$_createdActivities as $activity) {
            $activity_ids[] = $activity->id;
        }
        return $activity_ids;
    }
}
