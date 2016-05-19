<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */


/**
 * Class CalDavEventCollection
 *
 * @coversDefaultClass \CalDavEventCollection
 */
class CalDavEventCollectionCRYS1322Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @inheritdoc
     */
    public function setUp()
    {
        BeanFactory::setBeanClass('Meetings', 'CalDavEventCollectionMeetingCRYS1322');
        BeanFactory::setBeanClass('Users', 'UserCRYS1322');
        BeanFactory::setBeanClass('Contacts', 'ContactCRYS1322');
        BeanFactory::setBeanClass('EmailAddresses', 'EmailAddressCRYS1322');
        $GLOBALS['current_user'] = new UserCRYS1322();
        $GLOBALS['current_user']->retrieve('_user_id_');
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
    {
        $GLOBALS['current_user'] = null;
        BeanFactory::setBeanClass('Meetings');
        BeanFactory::setBeanClass('Users');
        BeanFactory::setBeanClass('Contacts');
        BeanFactory::setBeanClass('EmailAddresses');
    }

    /**
     * Provider for testPrepareForInvite
     *
     * @see CalDavEventCollectionCRYS1322Test::testPrepareForInvite
     */
    public function prepareForInviteProvider()
    {
        return array(
            'noChanges' => array(
                'emailInvitee' => null,
                'organizerEmail' => null,
            ),
            'setRSVP' => array(
                'emailInvitee' => 'attendee@test.com',
                'organizerEmail' => null,
            ),
            'organizerChangeEmail' => array(
                'emailInvitee' => null,
                'organizerEmail' => 'caldav@test.com',
            ),
            'allChanges' => array(
                'emailInvitee' => 'attendee@test.com',
                'organizerEmail' => 'caldav@test.com',
            ),
        );
    }

    /**
     * Test prepare for invite
     *
     * @param string $emailInvitee
     * @param string $organizerEmail
     *
     * @covers \CalDavEventCollection::prepareForInvite
     * @dataProvider prepareForInviteProvider
     */
    public function testPrepareForInvite($emailInvitee, $organizerEmail)
    {
        $meetingMock = BeanFactory::getBean('Meetings', '_meeting_id_');

        $result = CalDavEventCollection::prepareForInvite($meetingMock, $emailInvitee, $organizerEmail);

        $this->assertContains('X-SUGAR-ID:_meeting_id_', $result);
        $this->assertContains('X-SUGAR-NAME:Meetings', $result);

        if ($emailInvitee) {
            $this->assertContains('METHOD:REQUEST', $result);
            $this->assertContains(
                "ATTENDEE;PARTSTAT=NEEDS-ACTION;CN=Contact Test2;RSVP=TRUE:mailto:attendee@t\r\n est.com",
                $result
            );
        } else {
            $this->assertContains('ATTENDEE;PARTSTAT=NEEDS-ACTION;CN=Contact Test2:mailto:attendee@test.com', $result);
        }

        $this->assertContains(
            "ATTENDEE;PARTSTAT=ACCEPTED;CN=User Test1;ROLE=CHAIR:mailto:organizer@test.c\r\n om",
            $result
        );
        if ($organizerEmail) {
            $this->assertContains('ORGANIZER;CN=User Test1:mailto:' . $organizerEmail, $result);
        } else {
            $this->assertContains("ORGANIZER;CN=User Test1:mailto:organizer@test.com", $result);
        }
    }
}

/**
 * Stub class for Meeting bean
 */
class CalDavEventCollectionMeetingCRYS1322 extends \Meeting
{
    public function retrieve($id) {
        $this->populateFromRow(array(
            'name' => 'Meeting1102415055',
            'date_entered' => '2015-12-16 13:34:54',
            'date_modified' => '2015-12-16 13:34:54',
            'modified_user_id' => '_user_id_',
            'created_by' => '_user_id_',
            'description' => null,
            'deleted' => '0',
            'location' => null,
            'duration_hours' => '0',
            'duration_minutes' => '15',
            'date_start' => '2015-12-16 13:34:54',
            'date_end' => '2015-12-16 13:49:54',
            'parent_type' => null,
            'status' => 'Planned',
            'type' => 'Sugar',
            'parent_id' => null,
            'reminder_time' => '-1',
            'email_reminder_time' => '-1',
            'email_reminder_sent' => '0',
            'sequence' => '0',
            'repeat_type' => null,
            'repeat_interval' => '1',
            'repeat_dow' => null,
            'repeat_until' => null,
            'repeat_count' => null,
            'repeat_parent_id' => null,
            'recurring_source' => null,
            'assigned_user_id' => '_user_id_',
        ));

        $this->id = $id;
        $this->fetched_row = $this->toArray(true);
        return $this;
    }

    public function load_relationship($link_name)
    {
        if ($link_name == 'users') {
            $this->$link_name = new Link2UsersCRYS1322($link_name, $this);
            return true;
        }
        if ($link_name == 'contacts') {
            $this->$link_name = new Link2ContactsCRYS1322($link_name, $this);
            return true;
        }
        return false;
    }
}

/**
 * Stub class for Link2 bean
 */
class Link2UsersCRYS1322 extends Link2
{
    public function getBeans() {
        $this->rows = array(
            '_user_id_' => array('accept_status' => 'none')
        );
        return array(
            '_user_id_' => BeanFactory::getBean('Users', '_user_id_')
        );
    }

    public function load()
    {
        $this->rows = array(
            '_user_id_' => array('accept_status' => 'none')
        );
    }
}

/**
 * Stub class for Link2 bean
 */
class Link2ContactsCRYS1322 extends Link2
{
    public function getBeans() {
        $this->rows = array(
            '_contact_id_' => array('accept_status' => 'none')
        );
        return array(
            '_contact_id_' => BeanFactory::getBean('Contacts', '_contact_id_')
        );
    }

    public function load()
    {
        $this->rows = array(
            '_contact_id_' => array('accept_status' => 'none')
        );
    }
}

/**
 * Stub class for User bean
 */
class UserCRYS1322 extends User
{
    public function retrieve($id)
    {
        $this->populateFromRow(array(
            'first_name' => 'User',
            'last_name' => 'Test1',
        ));
        $this->id = $id;

        $this->emailAddress = BeanFactory::getBean('EmailAddresses', 'email_id_user');

        return $this;
    }
}

/**
 * Stub class for Contact bean
 */
class ContactCRYS1322 extends Contact
{
    public function retrieve($id)
    {
        $this->populateFromRow(array(
            'first_name' => 'Contact',
            'last_name' => 'Test2',
        ));
        $this->id = $id;

        $this->emailAddress = BeanFactory::getBean('EmailAddresses', 'email_id_contact');

        return $this;
    }
}

/**
 * Stub class for EmailAddress bean
 */
class EmailAddressCRYS1322 extends EmailAddress
{
    public function retrieve($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getPrimaryAddress($focus)
    {
        if ($focus instanceof UserCRYS1322) {
            return 'organizer@test.com';
        }

        return 'attendee@test.com';
    }
}
