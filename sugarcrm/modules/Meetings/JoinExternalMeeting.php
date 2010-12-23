<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

/*
 * This file checks if you are invited to an external meeting, which is too expensive
 * to do per-row in lists so we direct them here and check before either forwarding
 * them along, or displaying an error message to the user.
 */

global $db, $current_user, $mod_strings, $app_strings, $app_list_strings;

$ret = $db->query("SELECT id FROM meetings_users WHERE meeting_id = '".$db->quote($_REQUEST['meeting_id'])."' AND user_id = '".$current_user->id."' AND deleted = 0",true);
$row = $db->fetchByAssoc($ret);

$meetingBean = loadBean('Meetings');
$meetingBean->retrieve($_REQUEST['meeting_id']);

if ( isset($row['id']) ) {
    // Everything looks good, send them on their way.
    if ( $_REQUEST['host_meeting'] == '1' ) {
        SugarApplication::redirect($meetingBean->host_url);
    } else {
        SugarApplication::redirect($meetingBean->join_url);
    }
} else {
    // They weren't invited, don't let them in for tea and cake.
    // The cake is a lie anyways.
    $tplFile = 'modules/Meetings/tpls/extMeetingNotInvited.tpl';
    if ( file_exists('custom/'.$tplFile) ) {
        $tplFile = 'custom/'.$tplFile;
    }

    $ss = new Sugar_Smarty();
    $ss->assign('current_user',$current_user);
    $ss->assign('bean',$meetingBean->toArray());
    $ss->display($tplFile);
}