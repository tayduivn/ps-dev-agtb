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

require_once('modules/Meetings/views/view.listbytype.php');

/**
 * Bug50697Test.php
 * This test checks the alterations made to modules/Meetings/views/view.listbytype.php to remove the hard-coded
 * UTC_TIMESTAMP function that was used which appears to be MYSQL specific.  Changed to use timedate code instead
 *
 */
class Bug50697Test extends Sugar_PHPUnit_Framework_TestCase
{

public function setUp()
{
    global $current_user;
    $current_user = SugarTestUserUtilities::createAnonymousUser();
}

public function tearDown()
{
    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    unset($GLOBALS['current_user']);
}

/**
 * testProcessSearchForm
 *
 * Test the processSearchForm function which contained the offensive SQL
 */
public function testProcessSearchForm()
{
    global $timedate;
    $_REQUEST = array();
    $mlv = new MeetingsViewListbytype();
    $mlv->processSearchForm();
    $this->assertRegExp('/meetings\.date_start > \d{4}-\d{2}-\d{2} \d{1,2}:\d{2}:\d{2}/', $mlv->where, 'Failed to create datetime query for meetings.date_start');

    $_REQUEST['name_basic'] = 'Bug50697Test';
    $mlv->processSearchForm();
    $this->assertRegExp('/meetings\.date_start > \d{4}-\d{2}-\d{2} \d{1,2}:\d{2}:\d{2}/', $mlv->where, 'Failed to create datetime query for meetings.date_start');
    $this->assertRegExp('/meetings\.name LIKE \'Bug50697Test%\'/', $mlv->where, 'Failed to generate meetings.name search parameter');
}


}