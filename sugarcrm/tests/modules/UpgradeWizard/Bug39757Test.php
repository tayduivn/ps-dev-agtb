<?php
require_once('modules/UpgradeWizard/uw_utils.php');
require_once('modules/Meetings/Meeting.php');
class Bug39757Test extends Sugar_PHPUnit_Framework_TestCase {

	private $_meetingId;
	
	function setUp() {
		$id = create_guid();
		$sql = "INSERT INTO meetings (id, date_start, duration_hours, duration_minutes, date_end, deleted) VALUES('{$id}', '2010-10-11 23:45:00', 0, 30, '2010-10-12', 0)";
		$GLOBALS['db']->query($sql);
		$this->_meetingId = $id;
	}
	
	function tearDown() {
	    $sql = "DELETE FROM MEETINGS WHERE id = '{$this->_meetingId}'";
	    $GLOBALS['db']->query($sql);
	}
	
	
	function testEndDateChange() {	
	    $meetingsSql = "UPDATE meetings AS a INNER JOIN meetings AS b ON a.id = b.id SET a.date_end = date_add(b.date_start, INTERVAL + concat(b.duration_hours, b.duration_minutes) HOUR_MINUTE) WHERE a.id = '{$this->_meetingId}'";
		$GLOBALS['db']->query($meetingsSql);
		
		$meeting = new Meeting();
		$meeting->disable_row_level_security = true;
		$meeting->retrieve($this->_meetingId);
		$meeting->fixUpFormatting();
		$this->assertEquals($meeting->date_end, '2010-10-12 00:15:00', 'Ensuring that the end_date is saved properly as a date time field');
	}
}

?>