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

/**
 * SugarTestNotificationUtilities.php
 * This is a helper utility to create Notification bean instances for testing
 */
class SugarTestNotificationUtilities
{
    private static $_createdNotifications = array();

    public static function createNotification($id = '')
    {
        $time = mt_rand();
        $notification = BeanFactory::getBean('Notifications');
        $notification->name = 'SugarNotification' . $time;
        $notification->save();
        self::$_createdNotifications[] = $notification;
        return $notification;
    }

    public static function removeAllCreatedNotifications()
    {
        $notification_ids = self::getCreatedNotificationIds();
        
        if (!empty($Notification_ids))
        {
            $GLOBALS['db']->query('DELETE FROM notifications WHERE id IN (\'' . implode("', '", $notification_ids) . '\')');
            $GLOBALS['db']->query('DELETE FROM notifications_audit WHERE parent_id IN (\'' . implode("', '", $notification_ids) . '\')');
         }
    }
    
    public static function getCreatedNotificationIds()
    {
        $notification_ids = array();
        
        foreach (self::$_createdNotifications as $notification)
        {
            $notification_ids[] = $notification->id;
        }
        
        return $notification_ids;
    }
}