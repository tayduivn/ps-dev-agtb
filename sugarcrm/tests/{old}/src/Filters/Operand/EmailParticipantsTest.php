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

namespace Sugarcrm\SugarcrmTests\Filters\Operand;

use SugarTestContactUtilities;
use SugarTestEmailAddressUtilities;
use SugarTestHelper;
use SugarTestRestUtilities;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Filters\Operand\EmailParticipants;
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Filters\Operand\EmailParticipants
 */
class EmailParticipantsTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        parent::tearDownAfterClass();
    }

    public function operandProvider()
    {
        return [
            'operand: $from' => [
                '$from',
                'from',
            ],
            'operand: $to' => [
                '$to',
                'to',
            ],
            'operand: $cc' => [
                '$cc',
                'cc',
            ],
            'operand: $bcc' => [
                '$bcc',
                'bcc',
            ],
        ];
    }

    /**
     * @covers ::format
     * @covers \ApiHelper::getHelper
     * @covers \BeanFactory::retrieveBean
     * @covers \SugarBean::getModuleName
     * @covers \SugarBeanApiHelper::formatForApi
     * @dataProvider operandProvider
     */
    public function testFormat(string $op, string $link)
    {
        $contact1 = SugarTestContactUtilities::createContact();
        $emailAddress1 = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson(
            $contact1,
            $emailAddress1
        );

        $contact2 = SugarTestContactUtilities::createContact();

        $emailAddress2 = SugarTestEmailAddressUtilities::createEmailAddress();

        $filter = [
            [
                'parent_type' => 'Contacts',
                'parent_id' => $contact1->id,
                'email_address_id' => $emailAddress1->id,
            ],
            [
                'parent_type' => 'Contacts',
                'parent_id' => $contact2->id,
            ],
            [
                'email_address_id' => $emailAddress2->id,
            ],
        ];

        $api = SugarTestRestUtilities::getRestServiceMock();
        $operand = new EmailParticipants($api, $op, $filter);

        $actual = $operand->format();

        $expected = [
            [
                '_link' => $link,
                'parent_type' => 'Contacts',
                'parent_id' => $contact1->id,
                'parent_name' => $contact1->name,
                'parent' => [
                    'type' => 'Contacts',
                    'id' => $contact1->id,
                    'name' => $contact1->name,
                    '_acl' => [
                        'fields' => (object)[],
                    ],
                    '_erased_fields' => [],
                ],
                'email_address_id' => $emailAddress1->id,
                'email_address' => $emailAddress1->email_address,
                'email_addresses' => [
                    'id' => $emailAddress1->id,
                    'email_address' => $emailAddress1->email_address,
                    '_acl' => [
                        'fields' => (object)[],
                    ],
                    '_erased_fields' => [],
                ],
            ],
            [
                '_link' => $link,
                'parent_type' => 'Contacts',
                'parent_id' => $contact2->id,
                'parent_name' => $contact2->name,
                'parent' => [
                    'type' => 'Contacts',
                    'id' => $contact2->id,
                    'name' => $contact2->name,
                    '_acl' => [
                        'fields' => (object)[],
                    ],
                    '_erased_fields' => [],
                ],
            ],
            [
                '_link' => $link,
                'email_address_id' => $emailAddress2->id,
                'email_address' => $emailAddress2->email_address,
                'email_addresses' => [
                    'id' => $emailAddress2->id,
                    'email_address' => $emailAddress2->email_address,
                    '_acl' => [
                        'fields' => (object)[],
                    ],
                    '_erased_fields' => [],
                ],
            ],
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers ::format
     * @covers \ApiHelper::getHelper
     * @covers \SugarBean::getModuleName
     * @covers \SugarBeanApiHelper::formatForApi
     * @dataProvider operandProvider
     */
    public function testFormatWithCurrentUserIdMacro(string $op, string $link)
    {
        $filter = [
            [
                'parent_type' => 'Users',
                'parent_id' => '$current_user_id',
            ],
        ];

        $api = SugarTestRestUtilities::getRestServiceMock();
        $operand = new EmailParticipants($api, $op, $filter);

        $actual = $operand->format();

        $expected = [
            [
                '_link' => $link,
                'parent_type' => 'Users',
                'parent_id' => '$current_user_id',
                'parent_name' => $GLOBALS['current_user']->name,
                'parent' => [
                    'type' => 'Users',
                    'id' => '$current_user_id',
                    'name' => $GLOBALS['current_user']->name,
                    '_acl' => [
                        'fields' => (object)[
                            'pwd_last_changed' => [
                                'read' => 'yes',
                            ],
                            'last_login' => [
                                'read' => 'yes',
                            ],
                        ],
                        'edit' => 'yes',
                        'create' => 'yes',
                    ],
                    '_erased_fields' => [],
                ],
            ],
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers ::format
     * @covers \ApiHelper::getHelper
     * @covers \BeanFactory::retrieveBean
     * @dataProvider operandProvider
     * @expectedException \SugarApiExceptionNotFound
     */
    public function testFormatParentNotFound(string $op, string $link)
    {
        $filter = [
            [
                'parent_type' => 'Contacts',
                'parent_id' => Uuid::uuid1(),
            ],
        ];

        $api = SugarTestRestUtilities::getRestServiceMock();
        $operand = new EmailParticipants($api, $op, $filter);

        $actual = $operand->format();
    }

    /**
     * @covers ::format
     * @covers \ApiHelper::getHelper
     * @covers \BeanFactory::retrieveBean
     * @covers \SugarBeanApiHelper::formatForApi
     * @dataProvider operandProvider
     * @expectedException \SugarApiExceptionNotFound
     */
    public function testFormatEmailAddressNotFound(string $op, string $link)
    {
        $filter = [
            [
                'email_address_id' => Uuid::uuid1(),
            ],
        ];

        $api = SugarTestRestUtilities::getRestServiceMock();
        $operand = new EmailParticipants($api, $op, $filter);

        $actual = $operand->format();
    }
}
