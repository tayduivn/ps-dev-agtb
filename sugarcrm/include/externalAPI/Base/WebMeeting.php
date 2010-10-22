<?php

interface WebMeeting {
	public function scheduleMeeting($bean);
	public function unscheduleMeeting($meeting);
	public function inviteAttendee($meeting, $attendee);
	public function uninviteAttendee($attendee);
	public function listMyMeetings();
	public function getMeetingDetails($meeting);
}
