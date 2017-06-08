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

use Sugarcrm\Sugarcrm\Util\Uuid;

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
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        // Archived email sent by the current user to $contact.
        $data = [
            'state' => Email::STATE_ARCHIVED,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ];
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email->load_relationship('from_link');
        $email->from_link->add($this->createEmailParticipant($GLOBALS['current_user']));
        $email->load_relationship('to_link');
        $email->to_link->add($this->createEmailParticipant($contact));

        // Archived email sent by the current user to $user and $contact.
        $data = [
            'state' => Email::STATE_ARCHIVED,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ];
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email->load_relationship('from_link');
        $email->from_link->add($this->createEmailParticipant($GLOBALS['current_user']));
        $email->load_relationship('to_link');
        $email->to_link->add($this->createEmailParticipant($user));
        $email->load_relationship('cc_link');
        $email->cc_link->add($this->createEmailParticipant($contact));

        // Draft email owned by the current user.
        $data = [
            'state' => Email::STATE_DRAFT,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ];
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email->load_relationship('to_link');
        $email->to_link->add($this->createEmailParticipant($user));

        // Draft email owned by the current user to be sent to $contact.
        $data = [
            'state' => Email::STATE_DRAFT,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ];
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email->load_relationship('from_link');
        $email->from_link->add($this->createEmailParticipant($GLOBALS['current_user']));
        $email->load_relationship('cc_link');
        $email->cc_link->add($this->createEmailParticipant($contact));

        // Archived email sent by $user to the current user.
        $data = [
            'state' => Email::STATE_ARCHIVED,
            'assigned_user_id' => $user->id,
        ];
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email->load_relationship('from_link');
        $email->from_link->add($this->createEmailParticipant($user));
        $email->load_relationship('to_link');
        $email->to_link->add($this->createEmailParticipant($GLOBALS['current_user']));

        // Archived email sent by $contact to $user and the current user.
        $data = [
            'state' => Email::STATE_ARCHIVED,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ];
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email->load_relationship('from_link');
        $email->from_link->add($this->createEmailParticipant($contact));
        $email->load_relationship('to_link');
        $email->to_link->add($this->createEmailParticipant($user));
        $email->load_relationship('bcc_link');
        $email->bcc_link->add($this->createEmailParticipant($GLOBALS['current_user']));

        // Archived email sent by the current user to an email address.
        $data = [
            'state' => Email::STATE_ARCHIVED,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ];
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email->load_relationship('from_link');
        $email->from_link->add($this->createEmailParticipant($GLOBALS['current_user']));
        $email->load_relationship('to_link');
        $email->to_link->add($this->createEmailParticipant(null, $address));

        // Archived email sent by the contact to the user with the specified email address.
        $data = [
            'state' => Email::STATE_ARCHIVED,
            'assigned_user_id' => $user->id,
        ];
        $email = SugarTestEmailUtilities::createEmail('', $data);
        $email->load_relationship('from_link');
        $email->from_link->add($this->createEmailParticipant($contact));
        $email->load_relationship('to_link');
        $email->to_link->add($this->createEmailParticipant($user, $address));

        $args = [
            'module' => 'Emails',
            'filter' => [
                [
                    '$from' => [
                        [
                            'parent_type' => 'Users',
                            'parent_id' => '$current_user_id',
                        ],
                    ],
                ],
            ],
            'fields' => 'id,parent_name',
            'order_by' => 'parent_name:ASC',
        ];
        $response = $this->api->filterList($this->service, $args);
        $this->assertCount(5, $response['records'], 'All emails where the current user is the sender');

        $args = [
            'module' => 'Emails',
            'filter' => [
                [
                    '$from' => [
                        [
                            'parent_type' => 'Users',
                            'parent_id' => '$current_user_id',
                        ],
                    ],
                ],
                [
                    'state' => [
                        '$in' => ['Archived'],
                    ],
                ],
            ],
            'fields' => 'id,parent_name',
            'order_by' => 'parent_name:ASC',
        ];
        $response = $this->api->filterList($this->service, $args);
        $this->assertCount(3, $response['records'], 'All archived emails sent by the current user');

        $args = [
            'module' => 'Emails',
            'filter' => [
                [
                    '$from' => [
                        [
                            'parent_type' => 'Users',
                            'parent_id' => '$current_user_id',
                        ],
                        [
                            'parent_type' => 'Users',
                            'parent_id' => $user->id,
                        ],
                    ],
                ],
                [
                    'state' => [
                        '$in' => ['Archived'],
                    ],
                ],
            ],
            'fields' => 'id,parent_name',
            'order_by' => 'parent_name:ASC',
        ];
        $response = $this->api->filterList($this->service, $args);
        $this->assertCount(4, $response['records'], 'All archived emails sent by the current user or other user');

        $args = [
            'module' => 'Emails',
            'filter' => [
                [
                    '$from' => [
                        [
                            'parent_type' => 'Contacts',
                            'parent_id' => $contact->id,
                        ],
                    ],
                ],
            ],
            'fields' => 'id,parent_name',
            'order_by' => 'parent_name:ASC',
        ];
        $response = $this->api->filterList($this->service, $args);
        $this->assertCount(2, $response['records'], 'All emails sent by the contact');

        $args = [
            'module' => 'Emails',
            'filter' => [
                [
                    '$or' => [
                        [
                            '$to' => [
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => '$current_user_id',
                                ],
                            ],
                        ],
                        [
                            '$cc' => [
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => '$current_user_id',
                                ],
                            ],
                        ],
                        [
                            '$bcc' => [
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => '$current_user_id',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'state' => [
                        '$in' => ['Archived'],
                    ],
                ],
            ],
            'fields' => 'id,parent_name',
            'order_by' => 'parent_name:ASC',
        ];
        $response = $this->api->filterList($this->service, $args);
        $this->assertCount(2, $response['records'], 'All archived emails received by the current user');

        $args = [
            'module' => 'Emails',
            'filter' => [
                [
                    '$or' => [
                        [
                            '$to' => [
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => '$current_user_id',
                                ],
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => $user->id,
                                ],
                            ],
                        ],
                        [
                            '$cc' => [
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => '$current_user_id',
                                ],
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => $user->id,
                                ],
                            ],
                        ],
                        [
                            '$bcc' => [
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => '$current_user_id',
                                ],
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => $user->id,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'fields' => 'id,parent_name',
            'order_by' => 'parent_name:ASC',
        ];
        $response = $this->api->filterList($this->service, $args);
        $this->assertCount(5, $response['records'], 'All emails received by the current user or $user');

        $args = [
            'module' => 'Emails',
            'filter' => [
                [
                    '$or' => [
                        [
                            '$from' => [
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => '$current_user_id',
                                ],
                            ],
                        ],
                        [
                            '$to' => [
                                [
                                    'parent_type' => 'Contacts',
                                    'parent_id' => $contact->id,
                                ],
                            ],
                        ],
                        [
                            '$cc' => [
                                [
                                    'parent_type' => 'Contacts',
                                    'parent_id' => $contact->id,
                                ],
                            ],
                        ],
                        [
                            '$bcc' => [
                                [
                                    'parent_type' => 'Contacts',
                                    'parent_id' => $contact->id,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'fields' => 'id,parent_name',
            'order_by' => 'parent_name:ASC',
        ];
        $response = $this->api->filterList($this->service, $args);
        $this->assertCount(5, $response['records'], 'All emails sent by the current user or to the contact');

        $args = [
            'module' => 'Emails',
            'filter' => [
                [
                    '$or' => [
                        [
                            '$to' => [
                                [
                                    'email_address_id' => $address->id,
                                ],
                            ],
                        ],
                        [
                            '$cc' => [
                                [
                                    'email_address_id' => $address->id,
                                ],
                            ],
                        ],
                        [
                            '$bcc' => [
                                [
                                    'email_address_id' => $address->id,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'fields' => 'id,parent_name',
            'order_by' => 'parent_name:ASC',
        ];
        $response = $this->api->filterList($this->service, $args);
        $this->assertCount(2, $response['records'], 'All emails sent to the email address');

        $args = [
            'module' => 'Emails',
            'filter' => [
                [
                    '$or' => [
                        [
                            '$to' => [
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => $user->id,
                                    'email_address_id' => $address->id,
                                ],
                            ],
                        ],
                        [
                            '$cc' => [
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => $user->id,
                                    'email_address_id' => $address->id,
                                ],
                            ],
                        ],
                        [
                            '$bcc' => [
                                [
                                    'parent_type' => 'Users',
                                    'parent_id' => $user->id,
                                    'email_address_id' => $address->id,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'fields' => 'id,parent_name',
            'order_by' => 'parent_name:ASC',
        ];
        $response = $this->api->filterList($this->service, $args);
        $this->assertCount(1, $response['records'], 'All emails sent to the user with the specified email address');
    }

    public function throwsSugarApiExceptionInvalidParameterProvider()
    {
        return [
            'no_parent_id_or_email_address_id' => [
                [
                    [
                        '$from' => [
                            [
                                'parent_type' => 'Contacts',
                            ],
                        ],
                    ],
                ],
            ],
            'no_parent_id' => [
                [
                    [
                        '$from' => [
                            [
                                'parent_type' => 'Contacts',
                                'email_address_id' => Uuid::uuid1(),
                            ],
                        ],
                    ],
                ],
            ],
            'no_parent_type_or_email_address_id' => [
                [
                    [
                        '$from' => [
                            [
                                'parent_id' => Uuid::uuid1(),
                            ],
                        ],
                    ],
                ],
            ],
            'no_parent_type' => [
                [
                    [
                        '$from' => [
                            [
                                'parent_id' => Uuid::uuid1(),
                                'email_address_id' => Uuid::uuid1(),
                            ],
                        ],
                    ],
                ],
            ],
            'filter_is_empty' => [
                [
                    [
                        '$from' => [
                            [
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::addParticipantFilter
     * @dataProvider throwsSugarApiExceptionInvalidParameterProvider
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testAddParticipantFilter_ThrowsSugarApiExceptionInvalidParameter($def)
    {
        $args = [
            'module' => 'Emails',
            'filter' => $def,
            'fields' => 'id,parent_name',
            'order_by' => 'parent_name:ASC',
        ];
        $response = $this->api->filterList($this->service, $args);
    }

    /**
     * Sets up an EmailParticipants bean from the data on the bean and the email address so that it is ready to add to a
     * relationship.
     *
     * @param null|SugarBean $bean
     * @param null|SugarBean $address
     * @return SugarBean
     */
    private function createEmailParticipant($bean, $address = null)
    {
        $ep = BeanFactory::newBean('EmailParticipants');
        $ep->new_with_id = true;
        $ep->id = Uuid::uuid1();
        BeanFactory::registerBean($ep);

        if ($bean) {
            $ep->parent_type = $bean->getModuleName();
            $ep->parent_id = $bean->id;
        }

        if ($address) {
            $ep->email_address_id = $address->id;
        }

        return $ep;
    }
}
