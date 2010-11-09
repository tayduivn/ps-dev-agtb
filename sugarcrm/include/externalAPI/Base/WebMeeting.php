<?php

interface WebMeeting {
	public function scheduleMeeting($bean);
	public function unscheduleMeeting($bean);
	public function inviteAttendee($bean, $attendee, $sendInvites);
	public function uninviteAttendee($bean, $attendee);
	public function listMyMeetings();
	public function getMeetingDetails($bean);
}
