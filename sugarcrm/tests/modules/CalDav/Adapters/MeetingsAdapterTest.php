<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once 'tests/SugarTestCalDavUtilites.php';

use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings as MeetingAdapater;
use \Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper;

/**
 * CalDav bean tests
 * Class CalDavTest
 *
 * @coversDefaultClass \Dav\Cal\Hadlers
 */
class MeetingsAdapterTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $meetingIds = array();
    /**
     * set up new user
     */
    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        $GLOBALS['current_user']->setPreference('timezone', 'Europe/Moscow');
        $this->createAnonumouseUsers();
    }

    /**
     * remove all created data
     */
    public function tearDown()
    {
        SugarTestCalDavUtilities::deleteAllCreatedCalendars();
        SugarTestCalDavUtilities::deleteCreatedEvents();
        SugarTestMeetingUtilities::removeAllCreatedMeetingsWithRecuringById($this->meetingIds);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestContactUtilities::removeCreatedContactsEmailAddresses();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeCreatedLeadsEmailAddresses();
        SugarTestLeadUtilities::removeCreatedLeadsUsersRelationships();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        parent::tearDown();
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings::import
     */
    public function testSaveMeeting()
    {
        $vCalendarEventText = $this->getEventTemplate('vevent');

        /**@var CalDavEvent $calDavBean*/
        $calDavBean = SugarTestCalDavUtilities::createEvent(array('calendardata' => $vCalendarEventText));
        $parcipiantsUser = $calDavBean->getParticipants();
        /**@var Meeting $bean*/
        $bean = $calDavBean->getBean();
        $meetingAdapter = new MeetingAdapater();
        $result = $meetingAdapter->import($bean, $calDavBean);

        $this->assertTrue($result);
        $this->assertEquals($calDavBean->getTitle(), $bean->name);
        $bean->save();

        $this->addCreatedMeetingId($bean);

        if (!$calDavBean->parent_id) {
            $calDavBean->setBean($bean);
            $calDavBean->save();
        }
        /** @var \Meeting $meetingBean */
        $meetingBean = BeanFactory::getBean('Meetings', $bean->id);
        $this->addCreatedMeetingId($meetingBean);

        if ($parcipiantsUser['Users']) {
            $meetingUsersList = $meetingBean->get_meeting_users();
            $usersUniqueEmails = array();
            foreach ($meetingUsersList as $user) {
                $userPrimaryEmail = null;
                $result = $user->getUsersNameAndEmail();
                if (!empty($result['email'])) {
                    $userPrimaryEmail = $result['email'];
                }

                if ($userPrimaryEmail && !in_array($userPrimaryEmail, $usersUniqueEmails)) {
                    $usersUniqueEmails[] = $userPrimaryEmail;
                }
            }

            $this->assertCount(count($parcipiantsUser['Users']), $usersUniqueEmails);

            foreach ($meetingUsersList as $meetingUsers) {
                if (isset($parcipiantsUserEmail[$meetingUsers->id])) {
                    $this->assertEquals(
                        $parcipiantsUserEmail[$meetingUsers->id]['accept_status'],
                        $meetingUsers->accept_status
                    );
                }
            }
        }
        if ($parcipiantsUser['Contacts']) {
            $meetingBean->load_relationship('contacts');
            $meetingContactsList = $meetingBean->contacts->get();
            $this->assertEquals(array_keys($parcipiantsUser['Contacts']), $meetingContactsList);
        }

        if ($parcipiantsUser['Leads']) {
            $meetingBean->load_relationship('leads');
            $meetingLeadsList = $meetingBean->leads->get();
            $this->assertEquals(array_keys($parcipiantsUser['Leads']), $meetingLeadsList);
        }

        $this->assertEquals(strtotime($calDavBean->getStartDate()), strtotime($meetingBean->date_start));
        $this->assertEquals($calDavBean->getDurationHours(), $meetingBean->duration_hours);
        $this->assertEquals($calDavBean->getDurationMinutes(), $meetingBean->duration_minutes);

        $calendarEvents = new CalendarEvents();
        $childQuery = $calendarEvents->getChildrenQuery($meetingBean);

        $childEvents = $meetingBean->fetchFromQuery($childQuery);

        //childs events count - should be days difference between $meetingBean->date_start and untill_date
        $this->assertEquals(count($childEvents), 7);
        foreach ($childEvents as $event) {
            $this->assertEquals($event->name, $calDavBean->getTitle());
        }


        $calDavBean->setTitle('test new title', $calDavBean->setComponent('VEVENT'));
        $calDavBean->save();
        $calDavBean = BeanFactory::getBean($calDavBean->module_name, $calDavBean->id, array('use_cache' => false));

        $result = $meetingAdapter->import($meetingBean, $calDavBean);
        $this->assertFalse($result);
        $this->assertEquals($calDavBean->getTitle(), $meetingBean->name);
        $meetingBean->save();

        $childEvents = $meetingBean->fetchFromQuery($childQuery);
        $this->assertEquals(7, count($childEvents));
        foreach ($childEvents as $event) {
            $this->assertEquals($event->name, $calDavBean->getTitle());
        }

        $meetingBean->repeat_until = "2015-08-19";
        $result = $meetingAdapter->import($meetingBean, $calDavBean);
        $this->assertTrue($result);
        $meetingBean->save();
        $childEvents = $meetingBean->fetchFromQuery($childQuery);
        $this->assertEquals(7, count($childEvents));

        $eventsKeys = array_keys($childEvents);

        $meetingBean->mark_deleted($eventsKeys[0]);
        $meetingBean->mark_deleted($eventsKeys[2]);

        $result = $meetingAdapter->import($meetingBean, $calDavBean);
        $this->assertTrue($result);
        $meetingBean->save();
        $childEvents = $meetingBean->fetchFromQuery($childQuery, array(), array('cache' => false));
        $this->assertEquals(7, count($childEvents));

        //check participients status
        $meetingUsersList  = $meetingBean->get_meeting_users();
        foreach ($meetingUsersList as $meetingUsers) {
            if (isset($parcipiantsUser['Users'][$meetingUsers->id])) {
                $this->assertEquals($parcipiantsUser['Users'][$meetingUsers->id]['accept_status'], $meetingUsers->accept_status);
            }
        }
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings::import
     */
    public function testImportExistsMeeting()
    {
        $vCalendarEventText = $this->getEventTemplate('vevent');
        /**@var CalDavEvent $calDavBean*/
        $calDavBean = SugarTestCalDavUtilities::createEvent(array('calendardata' => $vCalendarEventText));

        /**@var Meeting $meetingBean*/
        $meetingBean = SugarTestMeetingUtilities::createMeeting('', null, array(
                'date_start' => '2015-08-06 10:00:00',
                'date_end' => '2015-08-06 11:00:00',
                'name' => 'Test meeting',
                'description' => 'Test meeting description',
                'duration_hours' => 1,
                'duration_minutes' => 0
        ));

        $davParticipants = $calDavBean->getParticipants();
        $meetingBean->users_arr = array_keys($davParticipants['Users']);
        $meetingBean->save();
        $calDavBean->setBean($meetingBean);
        $calDavBean->save();
        $this->addCreatedMeetingId($meetingBean);
        $meetingAdapter = new MeetingAdapater();

        $meetingAdapter->import($meetingBean, $calDavBean);
        $meetingBean->save();

        $meetingBean = \BeanFactory::getBean($meetingBean->module_name, $meetingBean->id, array('cache' => false));

        if (!$calDavBean->parent_id) {
            $calDavBean->setBean($meetingBean);
            $calDavBean->save();
        }

        $this->assertEquals($meetingBean, $calDavBean->getBean());

        $participantsIDs = array_keys($davParticipants['Users']);
        $participantsIDs[] = $GLOBALS['current_user']->id;
        $this->assertEquals($participantsIDs, $meetingBean->users_arr);
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings::export
     */
    public function testExportMeeting()
    {
        $dateHelper = new DateTimeHelper();
        $meetingAdapter = new MeetingAdapater();
        /**@var Meeting $meetingBean*/
        $meetingBean = SugarTestMeetingUtilities::createMeeting('', null, array(
            'date_start' => '2015-08-06 10:00:00',
            'date_end' => '2015-08-06 11:00:00',
            'name' => 'Test meeting',
            'description' => 'Test meeting description',
            'duration_hours' => 1,
            'duration_minutes' => 0
        ));
        /**@var \CalDavEvent $calDavEvent */
        $calDavEvent = \BeanFactory::getBean('CalDavEvents');

        $relatedCalDavBean = $calDavEvent->findByBean($meetingBean);
        $calDavBean = $relatedCalDavBean !== null ? $relatedCalDavBean : $calDavEvent;
        $meetingAdapter->export($meetingBean, $calDavBean);
        $this->assertEquals($dateHelper->sugarDateToUTC($meetingBean->date_start)->format(\TimeDate::DB_DATETIME_FORMAT), $calDavBean->getStartDate());
        $this->assertEquals($meetingBean->name, $calDavBean->getTitle());
        $this->assertEquals($meetingBean->description, $calDavBean->getDescription());
        $this->assertEquals($meetingBean->duration_hours, $calDavBean->getDurationHours());
        $calDavBean->save();
    }

    /**
     * Load template for event
     * @param string $templateName
     * @return string;
     */
    protected function getEventTemplate($templateName)
    {
        return file_get_contents(dirname(__FILE__).'/../EventTemplates/'.$templateName.'.ics');
    }

    /**
     * Add bean id to log
     * @param $meeting
     */
    private function addCreatedMeetingId($meeting)
    {
        if (!in_array($meeting->id, $this->meetingIds)) {
            $this->meetingIds[] = $meeting->id;
        }
    }

    /**
     * Create anonumouse users for test
     */
    private function createAnonumouseUsers()
    {
        $idUser1 = create_guid();
        $idUser2 = create_guid();
        $idUser3 = create_guid();

        $users = array(
            array('email1' => 'test@test.com', 'new_with_id' => true, 'id' => $idUser1),
        );

        $contacts = array(
            array('email' => 'test2@test.com', 'new_with_id' => true, 'id' => $idUser2)
        );

        $leads = array(
            array('email' => 'test1@test.com', 'new_with_id' => true, 'id' => $idUser3)
        );
        //print_r($users);
        foreach ($users as $user) {
            SugarTestUserUtilities::createAnonymousUser(true, 0, $user);
        }

        foreach ($contacts as $contact) {
            SugarTestContactUtilities::createContact($contact['id'], $contact);
        }

        foreach ($leads as $lead) {
            SugarTestLeadUtilities::createLead($lead['id'], $lead);
        }
    }
}
