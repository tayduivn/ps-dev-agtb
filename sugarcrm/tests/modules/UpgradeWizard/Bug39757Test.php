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
 
require_once('modules/UpgradeWizard/uw_utils.php');
require_once('modules/Meetings/Meeting.php');

/**
 * @ticket 39757
 */
class Bug39757Test extends Sugar_PHPUnit_Framework_TestCase 
{
	private $_meetingId;
	
	public function setUp() 
	{
		if ( $GLOBALS['db']->dbType != 'mysql' ) {
            $this->markTestSkipped('Only applies to MySQL');
		}
		$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
		$id = create_guid();
		$sql = "INSERT INTO meetings (id, date_start, duration_hours, duration_minutes, date_end, deleted) VALUES('{$id}', '2010-10-11 23:45:00', 0, 30, '2010-10-12', 0)";
		$GLOBALS['db']->query($sql);
		$this->_meetingId = $id;
	}
	
	public function tearDown() 
	{
	    $sql = "DELETE FROM MEETINGS WHERE id = '{$this->_meetingId}'";
	    $GLOBALS['db']->query($sql);
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
	}
	
	
	public function testEndDateChange() 
	{	
	    $meetingsSql = "UPDATE meetings AS a INNER JOIN meetings AS b ON a.id = b.id SET a.date_end = date_add(b.date_start, INTERVAL + concat(b.duration_hours, b.duration_minutes) HOUR_MINUTE) WHERE a.id = '{$this->_meetingId}'";
		$GLOBALS['db']->query($meetingsSql);
		
		$meeting = new Meeting();
		$meeting->disable_row_level_security = true;
		$meeting->retrieve($this->_meetingId);
		$meeting->fixUpFormatting();
		$this->assertEquals($meeting->date_end, '2010-10-12 00:15:00', 'Ensuring that the end_date is saved properly as a date time field');
	}
}