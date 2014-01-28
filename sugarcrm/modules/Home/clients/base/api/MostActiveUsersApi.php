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
        $db = DBManagerFactory::getInstance();

        // meetings
        $query = "SELECT meetings.assigned_user_id, count(meetings.id) meetings_count, users.first_name, users.last_name
                FROM meetings, users
                WHERE meetings.assigned_user_id = users.id
                    AND users.deleted = 0
                    AND meetings.status='Held'
                    AND " . $db->convert('meetings.date_modified', 'add_date', array($days, 'DAY')) . " > " . $db->convert('', 'today') . "
                GROUP BY meetings.assigned_user_id, users.first_name, users.last_name
                ORDER BY meetings_count DESC";

        $GLOBALS['log']->debug("Finding most active users for Meetings: ".$query);
        $result = $db->limitQuery($query, 0, 1);
        $meetings = array();
        
        if (false !== $row = $db->fetchByAssoc($result)) {
            if (!empty($row)) {
                $meetings['user_id'] = $row['assigned_user_id'];
                $meetings['count'] = $row['meetings_count'];
                $meetings['first_name'] = $row['first_name'];
                $meetings['last_name'] = $row['last_name'];
            }
        }

        // calls
        $query = "SELECT calls.assigned_user_id, count(calls.id) calls_count, users.first_name, users.last_name
                FROM calls, users
                WHERE calls.assigned_user_id = users.id
                    AND users.deleted = 0
                    AND calls.status='Held'
                    AND " . $db->convert('calls.date_modified', 'add_date', array($days, 'DAY')) . " > " . $db->convert('', 'today') . "
                GROUP BY calls.assigned_user_id, users.first_name, users.last_name
                ORDER BY calls_count DESC";

        $GLOBALS['log']->debug("Finding most active users for Calls: ".$query);
        $result = $db->limitQuery($query, 0, 1);
        $calls = array();

        if (false !== $row = $db->fetchByAssoc($result)) {
            if (!empty($row)) {
                $calls['user_id'] = $row['assigned_user_id'];
                $calls['count'] = $row['calls_count'];
                $calls['first_name'] = $row['first_name'];
                $calls['last_name'] = $row['last_name'];
            }
        }

        // inbound emails
        $query = "SELECT emails.assigned_user_id, count(emails.id) emails_count, users.first_name, users.last_name
                FROM emails, users
                WHERE emails.assigned_user_id = users.id
                    AND users.deleted = 0
                    AND emails.type = 'inbound'
                    AND " . $db->convert('emails.date_entered', 'add_date', array($days, 'DAY')) . " > " . $db->convert('', 'today') . "
                GROUP BY emails.assigned_user_id, users.first_name, users.last_name
                ORDER BY emails_count DESC";

        $GLOBALS['log']->debug("Finding most active users for Inbound Emails: ".$query);
        $result = $db->limitQuery($query, 0, 1);
        $inbounds = array();

        if (false !== $row = $db->fetchByAssoc($result)) {
            if (!empty($row)) {
                $inbounds['user_id'] = $row['assigned_user_id'];
                $inbounds['count'] = $row['emails_count'];
                $inbounds['first_name'] = $row['first_name'];
                $inbounds['last_name'] = $row['last_name'];
            }
        }

        // outbound emails
        $query = "SELECT emails.assigned_user_id, count(emails.id) emails_count, users.first_name, users.last_name
                FROM emails, users
                WHERE emails.assigned_user_id = users.id
                    AND users.deleted = 0
                    AND emails.status='sent'
                    AND emails.type = 'out'
                    AND " . $db->convert('emails.date_entered', 'add_date', array($days, 'DAY')) . " > " . $db->convert('', 'today') . "
                GROUP BY emails.assigned_user_id, users.first_name, users.last_name
                ORDER BY emails_count DESC";

        $GLOBALS['log']->debug("Finding most active users for Outbound Emails: ".$query);
        $result = $db->limitQuery($query, 0, 1);
        $outbounds = array();

        if (false !== $row = $db->fetchByAssoc($result)) {
            if (!empty($row)) {
                $outbounds['user_id'] = $row['assigned_user_id'];
                $outbounds['count'] = $row['emails_count'];
                $outbounds['first_name'] = $row['first_name'];
                $outbounds['last_name'] = $row['last_name'];
            }
        }

        return array(
            'meetings' => $meetings,
            'calls' => $calls,
            'inbound_emails' => $inbounds,
            'outbound_emails' => $outbounds
        );
    }
}
