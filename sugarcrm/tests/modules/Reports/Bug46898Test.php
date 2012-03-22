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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('modules/Reports/schedule/ReportSchedule.php');
require_once('modules/Reports/SavedReport.php');

/**
 * Bug #46898
 * Scheduled reports could not be sent to multiple users
 *
 * @group 46898
 * @author mgusev@sugarcrm.com
 */
class Bug46898Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function testGetReportsToEmail()
    {
        $user1 = SugarTestUserUtilities::createAnonymousUser();
        $user2 = SugarTestUserUtilities::createAnonymousUser();

        $savedReports = new SavedReport();
        $savedReports->name = 'Bug46898Report1';
        $savedReports->save();

        $reportSchedule = new ReportSchedule();
        $schedule1 = $reportSchedule->save_schedule(false, $user1->id, $savedReports->id, false, 1, true, 'bug');
        $schedule2 = $reportSchedule->save_schedule(false, $user2->id, $savedReports->id, false, 1, true, 'bug');
        $GLOBALS['db']->query("UPDATE {$reportSchedule->table_name} SET next_run='2001-01-01 00:00:00' WHERE id='{$schedule1}'");
        $GLOBALS['db']->query("UPDATE {$reportSchedule->table_name} SET next_run='2001-01-01 00:00:00' WHERE id='{$schedule2}'");

        $actual = $reportSchedule->get_reports_to_email('', 'bug');

        $ids = array();
        foreach ($actual as $item)
        {
            $ids[] = $item['user_id'];
        }

        $savedReports->mark_deleted($savedReports->id);
        $user1->mark_deleted($user1->id);
        $user2->mark_deleted($user2->id);
        $reportSchedule->mark_deleted($schedule1);
        $reportSchedule->mark_deleted($schedule2);
        $GLOBALS['db']->commit();

        $this->assertEquals(2, count($ids));
        $this->assertContains($user1->id, $ids, 'User is missed in returned array');
        $this->assertContains($user2->id, $ids, 'User is missed in returned array');
    }
}