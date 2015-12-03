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

use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Calls as CallAdapater;
use \Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper;

/**
 * CalDav bean tests
 * Class CalDavTest
 *
 * @coversDefaultClass \Dav\Cal\Hadlers
 */
class CallsAdapterTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $callIds = array();
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
        SugarTestCallUtilities::removeAllCreatedMeetingsWithRecuringById($this->callIds);
        SugarTestMeetingUtilities::removeAllCreatedMeetingsWithRecuringById($this->callIds);
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
    public function testSaveCall()
    {
        $vCalendarEventText = $this->getEventTemplate('vevent');
        $dateTimeHelper = new DateTimeHelper();
        /**@var CalDavEvent $calDavBean*/
        $calDavBean = SugarTestCalDavUtilities::createEvent(array('calendardata' => $vCalendarEventText));
        $calDavBean->parent_type = 'Calls';
        $parcipiantsUser = $calDavBean->getParticipants();
        /**@var \Call $bean*/
        $bean = $calDavBean->getBean();
        $callAdapter = new CallAdapater();
        $result = $callAdapter->import($bean, $calDavBean);

        $this->assertTrue($result);
        $this->assertEquals($calDavBean->getTitle(), $bean->name);
        $this->saveBean($bean);

        $this->addCreatedCallId($bean);

        if (!$calDavBean->parent_id) {
            $calDavBean->setBean($bean);
            $this->saveBean($calDavBean);
        }
        /** @var \Call $callBean */
        $callBean = BeanFactory::getBean('Calls', $bean->id);
        $this->addCreatedCallId($callBean);

        if ($parcipiantsUser['Users']) {
            $callUsersList = $callBean->get_call_users();
            $usersUniqueEmails = array();
            foreach ($callUsersList as $user) {
                $userPrimaryEmail = null;
                $result = $user->getUsersNameAndEmail();
                if (!empty($result['email'])) {
                    $userPrimaryEmail = $result['email'];
                }

                if ($userPrimaryEmail && !in_array($userPrimaryEmail, $usersUniqueEmails)) {
                    $usersUniqueEmails[] = $userPrimaryEmail;
                }
            }

            //$this->assertCount(count($parcipiantsUser['Users']), $usersUniqueEmails);

            foreach ($callUsersList as $meetingUsers) {
                if (isset($parcipiantsUserEmail[$meetingUsers->id])) {
                    $this->assertEquals(
                        $parcipiantsUserEmail[$meetingUsers->id]['accept_status'],
                        $meetingUsers->accept_status
                    );
                }
            }
        }
        if ($parcipiantsUser['Contacts']) {
            $callBean->load_relationship('contacts');
            $meetingContactsList = $callBean->contacts->get();
            $expected = array_keys($parcipiantsUser['Contacts']);
            sort($expected);
            sort($meetingContactsList);
            $this->assertEquals($expected, $meetingContactsList);
        }

        if ($parcipiantsUser['Leads']) {
            $callBean->load_relationship('leads');
            $meetingLeadsList = $callBean->leads->get();
            $expected = array_keys($parcipiantsUser['Leads']);
            sort($expected);
            sort($meetingLeadsList);
            $this->assertEquals($expected, $meetingLeadsList);
        }

        $this->assertEquals($calDavBean->getStartDate(), $dateTimeHelper->sugarDateToUTC($callBean->date_start)->format(\TimeDate::DB_DATETIME_FORMAT));
        $this->assertEquals($calDavBean->getDurationHours(), $callBean->duration_hours);
        $this->assertEquals($calDavBean->getDurationMinutes(), $callBean->duration_minutes);

        $calendarEvents = new CalendarEvents();
        $childQuery = $calendarEvents->getChildrenQuery($callBean);

        $childEvents = $callBean->fetchFromQuery($childQuery);

        //childs events count - should be days difference between $callBean->date_start and untill_date
        $this->assertEquals(count($childEvents), 7);
        foreach ($childEvents as $event) {
            $this->assertEquals($event->name, $calDavBean->getTitle());
        }


        $calDavBean->setTitle('test new title', $calDavBean->setComponent('VEVENT'));
        $this->saveBean($calDavBean);
        $calDavBean = BeanFactory::getBean($calDavBean->module_name, $calDavBean->id);

        $result = $callAdapter->import($callBean, $calDavBean);
        $this->assertTrue($result);
        $this->assertEquals($calDavBean->getTitle(), $callBean->name);
        $this->saveBean($callBean);

        $childEvents = $callBean->fetchFromQuery($childQuery);
        $this->assertEquals(7, count($childEvents));
        foreach ($childEvents as $event) {
            $this->assertEquals($event->name, $calDavBean->getTitle());
        }

        $callBean->repeat_until = "2015-08-19";
        $result = $callAdapter->import($callBean, $calDavBean);
        $this->assertTrue($result);
        $this->saveBean($callBean);
        $childEvents = $callBean->fetchFromQuery($childQuery);
        $this->assertEquals(7, count($childEvents));

        $eventsKeys = array_keys($childEvents);

        $callBean->mark_deleted($eventsKeys[0]);
        $callBean->mark_deleted($eventsKeys[2]);

        $result = $callAdapter->import($callBean, $calDavBean);
        $this->assertTrue($result);
        $this->saveBean($callBean);
        $childEvents = $callBean->fetchFromQuery($childQuery, array(), array('cache' => false));
        $this->assertEquals(7, count($childEvents));

        //check participients status
        $callUsersList  = $callBean->get_call_users();
        foreach ($callUsersList as $callUsers) {
            if (isset($parcipiantsUser['Users'][$callUsers->id])) {
                $this->assertEquals($parcipiantsUser['Users'][$callUsers->id]['accept_status'], $callUsers->accept_status);
            }
        }
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
    private function addCreatedCallId($call)
    {
        if (!in_array($call->id, $this->callIds)) {
            $this->callIds[] = $call->id;
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

    /**
     * @param SugarBean $bean
     * @return bool|string
     */
    protected function saveBean(\SugarBean $bean)
    {
        $bean->processed = true;
        return $bean->save();
    }
}
