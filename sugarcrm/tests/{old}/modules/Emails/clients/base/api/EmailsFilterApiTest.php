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
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass EmailsFilterApi
 * @group api
 * @group email
 */
class EmailsFilterApiTest extends TestCase
{
    protected $api;
    protected $service;

    protected function setUp()
    {
        SugarTestHelper::setUp('current_user');

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new EmailsFilterApi();
    }

    protected function tearDown()
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
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
        $email1 = SugarTestEmailUtilities::createEmail(Uuid::uuid1(), $data, false);
        $email1->load_relationship('from');
        $email1->from->add($this->createEmailParticipant($GLOBALS['current_user']));
        $email1->load_relationship('to');
        $email1->to->add($this->createEmailParticipant($contact));
        $email1->save();

        // Archived email sent by the current user to $user and $contact.
        $data = [
            'state' => Email::STATE_ARCHIVED,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ];
        $email2 = SugarTestEmailUtilities::createEmail(Uuid::uuid1(), $data, false);
        $email2->load_relationship('from');
        $email2->from->add($this->createEmailParticipant($GLOBALS['current_user']));
        $email2->load_relationship('to');
        $email2->to->add($this->createEmailParticipant($user));
        $email2->load_relationship('cc');
        $email2->cc->add($this->createEmailParticipant($contact));
        $email2->save();

        // Draft email owned by the current user.
        $data = [
            'state' => Email::STATE_DRAFT,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ];
        $email3 = SugarTestEmailUtilities::createEmail('', $data);
        $email3->load_relationship('to');
        $email3->to->add($this->createEmailParticipant($user));

        // Draft email owned by the current user to be sent to $contact.
        $data = [
            'state' => Email::STATE_DRAFT,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ];
        $email4 = SugarTestEmailUtilities::createEmail('', $data);
        $email4->load_relationship('from');
        $email4->from->add($this->createEmailParticipant($GLOBALS['current_user']));
        $email4->load_relationship('cc');
        $email4->cc->add($this->createEmailParticipant($contact));

        // Archived email sent by $user to the current user.
        $data = [
            'state' => Email::STATE_ARCHIVED,
            'assigned_user_id' => $user->id,
        ];
        $email5 = SugarTestEmailUtilities::createEmail(Uuid::uuid1(), $data, false);
        $email5->load_relationship('from');
        $email5->from->add($this->createEmailParticipant($user));
        $email5->load_relationship('to');
        $email5->to->add($this->createEmailParticipant($GLOBALS['current_user']));
        $email5->save();

        // Archived email sent by $contact to $user and the current user.
        $data = [
            'state' => Email::STATE_ARCHIVED,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ];
        $email6 = SugarTestEmailUtilities::createEmail(Uuid::uuid1(), $data, false);
        $email6->load_relationship('from');
        $email6->from->add($this->createEmailParticipant($contact));
        $email6->load_relationship('to');
        $email6->to->add($this->createEmailParticipant($user));
        $email6->load_relationship('bcc');
        $email6->bcc->add($this->createEmailParticipant($GLOBALS['current_user']));
        $email6->save();

        // Archived email sent by the current user to an email address.
        $data = [
            'state' => Email::STATE_ARCHIVED,
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ];
        $email7 = SugarTestEmailUtilities::createEmail(Uuid::uuid1(), $data, false);
        $email7->load_relationship('from');
        $email7->from->add($this->createEmailParticipant($GLOBALS['current_user']));
        $email7->load_relationship('to');
        $email7->to->add($this->createEmailParticipant(null, $address));
        $email7->save();

        // Archived email sent by the contact to the user with the specified email address.
        $data = [
            'state' => Email::STATE_ARCHIVED,
            'assigned_user_id' => $user->id,
        ];
        $email8 = SugarTestEmailUtilities::createEmail(Uuid::uuid1(), $data, false);
        $email8->load_relationship('from');
        $email8->from->add($this->createEmailParticipant($contact));
        $email8->load_relationship('to');
        $email8->to->add($this->createEmailParticipant($user, $address));
        $email8->save();

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
                    '$to' => [
                        [
                            'parent_type' => 'Users',
                            'parent_id' => $user->id,
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
            ],
            'fields' => 'id,parent_name,to_addrs',
            'order_by' => 'parent_name:ASC',
        ];
        $response = $this->api->filterList($this->service, $args);
        $this->assertCount(
            1,
            $response['records'],
            "Single email sent from: current user to: \$user and cc'd to: \$contact"
        );
        $this->assertSame(
            $email2->id,
            $response['records'][0]['id'],
            "Specific email sent from: current user to: \$user and cc'd to: \$contact"
        );

        $args = [
            'module' => 'Emails',
            'filter' => [
                [
                    '$and' => [
                        [
                            '$from' => [
                                [
                                    'parent_type' => 'Contacts',
                                    'parent_id' => $contact->id,
                                ],
                            ],
                        ],
                        [
                            '$to' => [
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
                            ],
                        ],
                    ],
                ],
            ],
            'fields' => 'id,parent_name,to_addrs',
            'order_by' => 'parent_name:ASC',
        ];
        $response = $this->api->filterList($this->service, $args);
        $this->assertCount(
            1,
            $response['records'],
            "Single email sent from: \$contact to: \$user and bcc'd to: Current User"
        );
        $this->assertSame(
            $email6->id,
            $response['records'][0]['id'],
            "Specific email sent from: \$contact to: \$user and bcc'd to: Current User"
        );

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
                    '$or' => [
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
            'fields' => 'id,name',
            'order_by' => 'name:ASC',
        ];
        $response = $this->api->filterList($this->service, $args);
        $this->assertCount(3, $response['records'], 'All emails sent by the current user and to the contact');

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
                    '$or' => [
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
                    'state' => [
                        '$in' => ['Archived'],
                    ],
                ],
            ],
            'fields' => 'id,name',
            'order_by' => 'name:ASC',
        ];
        $response = $this->api->filterList($this->service, $args);
        $this->assertCount(2, $response['records'], 'All archived emails sent by the current user and to the contact');

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
                    '$to' => [
                        [
                            'parent_type' => 'Contacts',
                            'parent_id' => $contact->id,
                        ],
                    ],
                    'state' => [
                        '$in' => ['Draft'],
                    ],
                ],
            ],
            'fields' => 'id,name',
            'order_by' => 'name:ASC',
        ];
        $response = $this->api->filterList($this->service, $args);
        $this->assertCount(
            0,
            $response['records'],
            'All drafts to be sent by the current user and directly to the contact'
        );

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
                    '$or' => [
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
                    'state' => [
                        '$in' => ['Draft'],
                    ],
                ],
            ],
            'fields' => 'id,name',
            'order_by' => 'name:ASC',
        ];
        $response = $this->api->filterList($this->service, $args);
        $this->assertCount(1, $response['records'], 'All drafts to be sent by the current user and to the contact');
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
