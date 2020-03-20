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

namespace Sugarcrm\SugarcrmTests\SugarConnect\LogicHooks;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\SugarConnect\Client\Client;
use Sugarcrm\Sugarcrm\SugarConnect\Configuration\Configuration;
use Sugarcrm\Sugarcrm\SugarConnect\Event\Event;
use Sugarcrm\Sugarcrm\SugarConnect\LogicHooks\Handler;

class HandlerTest extends TestCase
{
    protected function setUp() : void
    {
        \SugarTestHelper::setUp('beanList');
        \SugarTestHelper::setUp('beanFiles');
        \SugarTestHelper::setUp('current_user');
    }

    protected function tearDown() : void
    {
        \SugarTestCallUtilities::removeAllCreatedCalls();
        \SugarTestMeetingUtilities::removeAllCreatedMeetings();
        \SugarTestContactUtilities::removeAllCreatedContacts();
        \SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    /**
     * The following events are published:
     *
     * 1. after_save: meeting
     * 2. after_save: call
     * 3. after_relationship_add: meeting, contact
     * 4. after_relationship_delete: meeting, contact
     * 5. after_relationship_add: call, contact
     * 6. after_relationship_delete: call, contact
     * 7. after_delete: meeting
     * 8. after_delete: call
     *
     * Then there are 4 additional events that are published while deleting the
     * meeting and call. There are two after_relationship_delete events
     * triggered while deleting the meeting to unlink the meeting from its teams
     * over team_link and team_count_link. And the same while deleting the call.
     * Hence, 12 events are published in all.
     */
    public function testPublish_SugarConnectIsEnabled() : void
    {
        $config = new Configuration();
        $config->enable();

        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(12))->method('send');

        Event::setClient($client);

        $this->performTest();
    }

    public function testPublish_SugarConnectIsDisabled() : void
    {
        $config = new Configuration();
        $config->disable();

        $client = $this->createMock(Client::class);
        $client->expects($this->never())->method('send');

        Event::setClient($client);

        $this->performTest();
    }

    protected function performTest() : void
    {
        $account = \SugarTestAccountUtilities::createAccount();
        $contact = \SugarTestContactUtilities::createContact();
        $meeting = \SugarTestMeetingUtilities::createMeeting();
        $call = \SugarTestCallUtilities::createCall();

        $account->load_relationship('contacts');
        $account->contacts->add($contact);

        $meeting->load_relationship('contacts');
        $meeting->contacts->add($contact);
        $meeting->contacts->delete($meeting, $contact->id);

        // The contact isn't removed from the meeting's link during an unlink.
        // The link has to be reset to remove the contact from the link's
        // memory. Otherwise, an additional event would be published after
        // removing the contact again when the meeting is deleted.
        $meeting->contacts->resetLoaded();

        $call->load_relationship('contacts');
        $call->contacts->add($contact);
        $call->contacts->delete($call, $contact->id);

        // The contact isn't removed from the call's link during an unlink. The
        // link has to be reset to remove the contact from the link's memory.
        // Otherwise, an additional event would be published after removing the
        // contact again when the call is deleted.
        $call->contacts->resetLoaded();

        $meeting->mark_deleted($meeting->id);
        $call->mark_deleted($call->id);
    }
}
