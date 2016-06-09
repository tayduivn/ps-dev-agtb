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

require_once 'modules/Emails/clients/base/api/EmailsFilterApi.php';

/**
 * @coversDefaultClass EmailsFilterApi
 * @group api
 * @group email
 */
class EmailsFilterApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $api;
    protected $service;

    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new EmailsFilterApi();
    }

    protected function tearDown()
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        parent::tearDown();
    }

    public function testRegisterApiRest()
    {
        $endpoints = $this->api->registerApiRest();

        $path = implode('/', $endpoints['filterModuleGet']['path']);
        $this->assertEquals('Emails/filter', $path);
        $this->assertEquals(
            'modules/Emails/clients/base/api/help/emails_filter_get_help.html',
            $endpoints['filterModuleGet']['longHelp']
        );

        $path = implode('/', $endpoints['filterModuleAll']['path']);
        $this->assertEquals('Emails', $path);
        $this->assertEquals(
            'modules/Emails/clients/base/api/help/emails_filter_get_help.html',
            $endpoints['filterModuleAll']['longHelp']
        );

        $path = implode('/', $endpoints['filterModuleAllCount']['path']);
        $this->assertEquals('Emails/count', $path);
        $this->assertEquals(
            'modules/Emails/clients/base/api/help/emails_filter_get_help.html',
            $endpoints['filterModuleAllCount']['longHelp']
        );

        $path = implode('/', $endpoints['filterModulePost']['path']);
        $this->assertEquals('Emails/filter', $path);

        $path = implode('/', $endpoints['filterModulePostCount']['path']);
        $this->assertEquals('Emails/filter/count', $path);

        $path = implode('/', $endpoints['filterModuleCount']['path']);
        $this->assertEquals('Emails/filter/count', $path);
    }

    /**
     * @covers ::filterList
     * @covers ::filterListSetup
     * @covers ::addFilters
     * @covers ::addFilter
     * @covers ::addFromFilter
     * @covers ::runQuery
     */
    public function testAddFromFilter()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $contact = SugarTestContactUtilities::createContact();

        // Archived email sent by the current user.
        $data = array(
            'state' => Email::EMAIL_STATE_ARCHIVED,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        );
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email->load_relationship('users_from');
        $email->users_from->add($GLOBALS['current_user']);

        // Archived email sent by the current user.
        $data = array(
            'state' => Email::EMAIL_STATE_ARCHIVED,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        );
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email->load_relationship('users_from');
        $email->users_from->add($GLOBALS['current_user']);

        // Draft email owned by the current user.
        $data = array(
            'state' => Email::EMAIL_STATE_DRAFT,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        );
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email->load_relationship('users_from');
        $email->users_from->add($GLOBALS['current_user']);

        // Archived email sent by $user.
        $data = array(
            'state' => Email::EMAIL_STATE_ARCHIVED,
            'assigned_user_id' => $user->id,
        );
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email->load_relationship('users_from');
        $email->users_from->add($user);

        // Draft email owned by $user.
        $data = array(
            'state' => Email::EMAIL_STATE_DRAFT,
            'assigned_user_id' => $user->id,
        );
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email->load_relationship('users_from');
        $email->users_from->add($user);

        // Archived email sent by $contact.
        $data = array(
            'state' => Email::EMAIL_STATE_ARCHIVED,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        );
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email->load_relationship('contacts_from');
        $email->contacts_from->add($contact);

        // All emails where the current user is the sender.
        $args = array(
            'module' => 'Emails',
            'filter' => array(
                array(
                    '$from' => array(
                        array(
                            'participant_module' => 'Users',
                            'participant_id' => '$current_user_id',
                        ),
                    ),
                ),
            ),
            'fields' => 'id,name',
            'order_by' => 'name:ASC',
        );
        $response = $this->api->filterList($this->service, $args);
        $this->assertCount(3, $response['records'], 'The current user is the sender on 3 emails');

        // All archived emails where the current user is the sender.
        $args = array(
            'module' => 'Emails',
            'filter' => array(
                array(
                    '$from' => array(
                        array(
                            'participant_module' => 'Users',
                            'participant_id' => '$current_user_id',
                        ),
                    ),
                ),
                array(
                    'state' => array(
                        '$in' => array('Archived'),
                    ),
                ),
            ),
            'fields' => 'id,name',
            'order_by' => 'name:ASC',
        );
        $response = $this->api->filterList($this->service, $args);
        $this->assertCount(2, $response['records'], 'The current user is the sender on 2 archived emails');

        // All archived emails where the current user or other user is the sender.
        $args = array(
            'module' => 'Emails',
            'filter' => array(
                array(
                    '$from' => array(
                        array(
                            'participant_module' => 'Users',
                            'participant_id' => '$current_user_id',
                        ),
                        array(
                            'participant_module' => 'Users',
                            'participant_id' => $user->id,
                        ),
                    ),
                ),
                array(
                    'state' => array(
                        '$in' => array('Archived'),
                    ),
                ),
            ),
            'fields' => 'id,name',
            'order_by' => 'name:ASC',
        );
        $response = $this->api->filterList($this->service, $args);
        $this->assertCount(3, $response['records'], 'The current user or $user is the sender on 3 archived emails');

        // All emails where the contact is the sender.
        $args = array(
            'module' => 'Emails',
            'filter' => array(
                array(
                    '$from' => array(
                        array(
                            'participant_module' => 'Contacts',
                            'participant_id' => $contact->id,
                        ),
                    ),
                ),
            ),
            'fields' => 'id,name',
            'order_by' => 'name:ASC',
        );
        $response = $this->api->filterList($this->service, $args);
        $this->assertCount(1, $response['records'], '$contact is the sender on 1 email');
    }
}
