<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('include/api/SugarApi.php');

class MostActiveUsersApi extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            'mostactiveusers' => array(
                'reqType' => 'GET',
                'path' => array('mostactiveusers'),
                'pathVars' => array(),
                'method' => 'getMostActiveUsers',
                'shortHelp' => 'Returns most active users',
                'longHelp' => 'modules/Home/clients/base/api/help/MostActiveUsersApi.html',
            ),
        );
    }

    /**
     * Returns most active users for last n days
     * @param $api
     * @param $args
     * @return array
     */
    public function getMostActiveUsers($api, $args) {
        $days = isset($args['days']) ? (int) $args['days'] : 30;

        // meetings
        $query = "SELECT meetings.assigned_user_id user_id, count(meetings.id) count, users.first_name, users.last_name FROM meetings, users WHERE meetings.assigned_user_id = users.id AND users.deleted = 0 AND meetings.status='Held' AND meetings.date_modified > (CURDATE() - INTERVAL ".$days." DAY) GROUP BY user_id ORDER BY count DESC limit 1";
        $GLOBALS['log']->debug("Finding most active users for Meetings: ".$query);
        $result = $GLOBALS['db']->query($query);
        $meetings = array();
        
        if (!empty($result)) {
            $row = $GLOBALS['db']->fetchByAssoc($result);
            if (!empty($row)) {
                $meetings = $row;
            }
        }

        // calls
        $query = "SELECT calls.assigned_user_id user_id, count(calls.id) count, users.first_name, users.last_name FROM calls, users WHERE calls.assigned_user_id = users.id AND users.deleted = 0 AND calls.status='Held' AND calls.date_modified > (CURDATE() - INTERVAL ".$days." DAY) GROUP BY user_id ORDER BY count DESC limit 1";
        $GLOBALS['log']->debug("Finding most active users for Calls: ".$query);
        $result = $GLOBALS['db']->query($query);
        $calls = array();
        
        if (!empty($result)) {
            $row = $GLOBALS['db']->fetchByAssoc($result);
            if (!empty($row)) {
                $calls = $row;
            }
        }
        
        // inbound emails
        $query = "SELECT emails.assigned_user_id user_id, count(emails.id) count, users.first_name, users.last_name FROM emails, users WHERE emails.assigned_user_id = users.id AND users.deleted = 0 AND emails.type = 'inbound' AND emails.date_entered > (CURDATE() - INTERVAL ".$days." DAY) GROUP BY user_id ORDER BY count DESC limit 1";
        $GLOBALS['log']->debug("Finding most active users for Inbound Emails: ".$query);
        $result = $GLOBALS['db']->query($query);
        $inbounds = array();
        
        if (!empty($result)) {
            $row = $GLOBALS['db']->fetchByAssoc($result);
            if (!empty($row)) {
                $inbounds = $row;
            }
        }
        
        // outbound emails
        $query = "SELECT emails.assigned_user_id user_id, count(emails.id) count, users.first_name, users.last_name FROM emails, users WHERE emails.assigned_user_id = users.id AND users.deleted = 0 AND emails.status='sent' AND emails.type = 'out' AND emails.date_entered > (CURDATE() - INTERVAL ".$days." DAY) GROUP BY user_id ORDER BY count DESC limit 1";
        $GLOBALS['log']->debug("Finding most active users for Outbound Emails: ".$query);
        $result = $GLOBALS['db']->query($query);
        $outbounds = array();
        
        if (!empty($result)) {
            $row = $GLOBALS['db']->fetchByAssoc($result);
            if (!empty($row)) {
                $outbounds = $row;
            }
        }
        
        return array('meetings' => $meetings, 'calls' => $calls, 'inbound_emails' => $inbounds, 'outbound_emails' => $outbounds);
    }
}
