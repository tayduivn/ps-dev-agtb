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
     * @covers ::addParticipantFilter
     * @covers ::runQuery
     */
    public function testAddParticipantFilter()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $contact = SugarTestContactUtilities::createContact();

        // Archived email sent by the current user to $contact.
        $data = array(
            'state' => Email::EMAIL_STATE_ARCHIVED,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        );
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email->load_relationship('users_from');
        $email->users_from->add($GLOBALS['current_user']);
        $email->load_relationship('contacts_to');
        $email->contacts_to->add($contact);

        // Archived email sent by the current user to $user and $contact.
        $data = array(
            'state' => Email::EMAIL_STATE_ARCHIVED,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        );
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email->load_relationship('users_from');
        $email->users_from->add($GLOBALS['current_user']);
        $email->load_relationship('users_to');
        $email->users_to->add($user);
        $email->load_relationship('contacts_cc');
        $email->contacts_cc->add($contact);

        // Draft email owned by the current user.
        $data = array(
            'state' => Email::EMAIL_STATE_DRAFT,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        );
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email->load_relationship('users_from');
        $email->users_from->add($GLOBALS['current_user']);
        $email->load_relationship('users_to');
        $email->users_to->add($user);

        // Draft email owned by the current user to be sent to $contact.
        $data = array(
            'state' => Email::EMAIL_STATE_DRAFT,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        );
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email->load_relationship('users_from');
        $email->users_from->add($GLOBALS['current_user']);
        $email->load_relationship('contacts_cc');
        $email->contacts_cc->add($contact);

        // Archived email sent by $user to the current user.
        $data = array(
            'state' => Email::EMAIL_STATE_ARCHIVED,
            'assigned_user_id' => $user->id,
        );
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email->load_relationship('users_from');
        $email->users_from->add($user);
        $email->load_relationship('users_to');
        $email->users_to->add($GLOBALS['current_user']);

        // Draft email owned by $user.
        $data = array(
            'state' => Email::EMAIL_STATE_DRAFT,
            'assigned_user_id' => $user->id,
        );
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email->load_relationship('users_from');
        $email->users_from->add($user);

        // Archived email sent by $contact to $user and the current user.
        $data = array(
            'state' => Email::EMAIL_STATE_ARCHIVED,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        );
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email->load_relationship('contacts_from');
        $email->contacts_from->add($contact);
        $email->load_relationship('users_to');
        $email->users_to->add($user);
        $email->load_relationship('users_bcc');
        $email->users_bcc->add($GLOBALS['current_user']);

        // Draft email to be sent by $user to $contact.
        $data = array(
            'state' => Email::EMAIL_STATE_DRAFT,
            'assigned_user_id' => $user->id,
        );
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email->load_relationship('users_from');
        $email->users_from->add($user);
        $email->load_relationship('contacts_to');
        $email->contacts_to->add($contact);

        $args = array(
            'module' => 'Emails',
            'filter' => array(
                array(
                    '$from' => array(
                        array(
                            'bean_type' => 'Users',
                            'bean_id' => '$current_user_id',
                        ),
                    ),
                ),
            ),
            'fields' => 'id,name',
            'order_by' => 'name:ASC',
        );
        $response = $this->api->filterList($this->service, $args);
        $this->assertCount(4, $response['records'], 'All emails where the current user is the sender');

        $args = array(
            'module' => 'Emails',
            'filter' => array(
                array(
                    '$from' => array(
                        array(
                            'bean_type' => 'Users',
                            'bean_id' => '$current_user_id',
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
        $this->assertCount(2, $response['records'], 'All archived emails sent by the current user');

        $args = array(
            'module' => 'Emails',
            'filter' => array(
                array(
                    '$from' => array(
                        array(
                            'bean_type' => 'Users',
                            'bean_id' => '$current_user_id',
                        ),
                        array(
                            'bean_type' => 'Users',
                            'bean_id' => $user->id,
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
        $this->assertCount(3, $response['records'], 'All archived emails sent by the current user or other user');

        $args = array(
            'module' => 'Emails',
            'filter' => array(
                array(
                    '$from' => array(
                        array(
                            'bean_type' => 'Contacts',
                            'bean_id' => $contact->id,
                        ),
                    ),
                ),
            ),
            'fields' => 'id,name',
            'order_by' => 'name:ASC',
        );
        $response = $this->api->filterList($this->service, $args);
        $this->assertCount(1, $response['records'], 'All emails sent by the contact');

        $args = array(
            'module' => 'Emails',
            'filter' => array(
                array(
                    '$or' => array(
                        array(
                            '$to' => array(
                                array(
                                    'bean_type' => 'Users',
                                    'bean_id' => '$current_user_id',
                                ),
                            ),
                        ),
                        array(
                            '$cc' => array(
                                array(
                                    'bean_type' => 'Users',
                                    'bean_id' => '$current_user_id',
                                ),
                            ),
                        ),
                        array(
                            '$bcc' => array(
                                array(
                                    'bean_type' => 'Users',
                                    'bean_id' => '$current_user_id',
                                ),
                            ),
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
        $this->assertCount(2, $response['records'], 'All archived emails received by the current user');

        $args = array(
            'module' => 'Emails',
            'filter' => array(
                array(
                    '$or' => array(
                        array(
                            '$to' => array(
                                array(
                                    'bean_type' => 'Users',
                                    'bean_id' => '$current_user_id',
                                ),
                                array(
                                    'bean_type' => 'Users',
                                    'bean_id' => $user->id,
                                ),
                            ),
                        ),
                        array(
                            '$cc' => array(
                                array(
                                    'bean_type' => 'Users',
                                    'bean_id' => '$current_user_id',
                                ),
                                array(
                                    'bean_type' => 'Users',
                                    'bean_id' => $user->id,
                                ),
                            ),
                        ),
                        array(
                            '$bcc' => array(
                                array(
                                    'bean_type' => 'Users',
                                    'bean_id' => '$current_user_id',
                                ),
                                array(
                                    'bean_type' => 'Users',
                                    'bean_id' => $user->id,
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            'fields' => 'id,name',
            'order_by' => 'name:ASC',
        );
        $response = $this->api->filterList($this->service, $args);
        $this->assertCount(4, $response['records'], 'All emails received by the current user or $user');

        $args = array(
            'module' => 'Emails',
            'filter' => array(
                array(
                    '$or' => array(
                        array(
                            '$from' => array(
                                array(
                                    'bean_type' => 'Users',
                                    'bean_id' => '$current_user_id',
                                ),
                            ),
                        ),
                        array(
                            '$to' => array(
                                array(
                                    'bean_type' => 'Contacts',
                                    'bean_id' => $contact->id,
                                ),
                            ),
                        ),
                        array(
                            '$cc' => array(
                                array(
                                    'bean_type' => 'Contacts',
                                    'bean_id' => $contact->id,
                                ),
                            ),
                        ),
                        array(
                            '$bcc' => array(
                                array(
                                    'bean_type' => 'Contacts',
                                    'bean_id' => $contact->id,
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            'fields' => 'id,name',
            'order_by' => 'name:ASC',
        );
        $response = $this->api->filterList($this->service, $args);
        $this->assertCount(4, $response['records'], 'All emails sent by the current user or to the contact');
    }
}
