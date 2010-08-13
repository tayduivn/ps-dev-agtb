<?php

abstract class WebMeeting {
	
	abstract function scheduleMeeting($name, $startdate, $duration, $password);
	
	abstract function unscheduleMeeting($meeting);
	
	abstract function joinMeeting($meeting, $attendeeName);
	
	abstract function inviteAttendee($meeting, $attendee);
	
	abstract function uninviteAttendee($attendee);
	
	abstract function listMyMeetings();
	
	abstract function getMeetingDetails($meeting);

	
}
