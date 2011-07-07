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
 
class SugarTestTrackerUtility
{
    private static $_trackerSettings = array();
    private static $_monitorId = '';
    
    private function __construct() {}
    
    public static function setup()
    {
        require('modules/Trackers/config.php');
        foreach($tracker_config as $entry) {
            if(isset($entry['bean'])) {
                $GLOBALS['tracker_' . $entry['name']] = false;
            } //if
        } //foreach
        
        $result = $GLOBALS['db']->query("SELECT category, name, value from config WHERE category = 'tracker' and name != 'prune_interval'");
        while($row = $GLOBALS['db']->fetchByAssoc($result)){
            self::$_trackerSettings[$row['name']] = $row['value'];
            $GLOBALS['db']->query("DELETE FROM config WHERE category = 'tracker' AND name = '{$row['name']}'");
        }
    }
    
    public static function restore()
    {
        foreach(self::$_trackerSettings as $name=>$value) {
            $GLOBALS['db']->query("INSERT INTO config (category, name, value) VALUES ('tracker', '{$name}', '{$value}')");
        }
    }
    
    public static function insertTrackerEntry($bean, $action)
    {
        require_once('modules/Trackers/TrackerManager.php');
        $trackerManager = TrackerManager::getInstance();
        $timeStamp = gmdate($GLOBALS['timedate']->get_db_date_time_format());
        $_REQUEST['action'] = $action;
        if($monitor = $trackerManager->getMonitor('tracker'))
        {
            //BEGIN SUGARCRM flav=pro ONLY
            $monitor->setValue('team_id', $GLOBALS['current_user']->getPrivateTeamID());
            //END SUGARCRM flav=pro ONLY
            $monitor->setValue('action', $action);
            $monitor->setValue('user_id', $GLOBALS['current_user']->id);
            $monitor->setValue('module_name', $bean->module_dir);
            $monitor->setValue('date_modified', $timeStamp);
            $monitor->setValue('visible', (($action == 'detailview') || ($action == 'editview')
            //BEGIN SUGARCRM flav=pro ONLY
                                            || ($action == 'wirelessdetail') || ($action == 'wirelessedit')
            //END SUGARCRM flav=pro ONLY
                                            ) ? 1 : 0);

            if (!empty($bean->id))
            {
                $monitor->setValue('item_id', $bean->id);
                $monitor->setValue('item_summary', $bean->get_summary_text());
            }

            //If visible is true, but there is no bean, do not track (invalid/unauthorized reference)
            //Also, do not track save actions where there is no bean id
            if($monitor->visible && empty($bean->id))
            {
               $trackerManager->unsetMonitor($monitor);
               return false;
            }
            $trackerManager->saveMonitor($monitor, true, true);
            if(empty(self::$_monitorId))
            {
                self::$_monitorId = $monitor->monitor_id;
            }
        }
    }
    
    public static function removeAllTrackerEntries()
    {
        if(!empty(self::$_monitorId))
        {
            $GLOBALS['db']->query("DELETE FROM tracker WHERE monitor_id = '".self::$_monitorId."'");
        }
    }
}
?>
