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

namespace Sugarcrm\SugarcrmTestsUnit\data\acl;

/**
 * @coversDefaultClass \SugarACLArchivedEmails
 */
class SugarACLArchivedEmailsTest extends \PHPUnit_Framework_TestCase
{
    public function checkAccessNoTestProvider()
    {
        return [
            ['access'],
            ['team_security'],
            ['list'],
            ['view'],
        ];
    }

    /**
     * @covers ::checkAccess
     * @dataProvider checkAccessNoTestProvider
     */
    public function testCheckAccess_AllowOtherChecksToDetermineAccess($view)
    {
        $bean = $this->createMock('\\Email');
        $bean->state = \Email::STATE_ARCHIVED;

        $context = [
            'action' => $view,
            'bean' => $bean,
        ];

        $acl = new \SugarACLArchivedEmails();
        $actual = $acl->checkAccess('Emails', $view, $context);
        $this->assertTrue($actual);
    }

    public function checkFieldAccessReadableProvider()
    {
        return [
            ['id'],
            ['date_entered'],
            ['date_modified'],
            ['assigned_user_id'],
            ['modified_user_id'],
            ['created_by'],
            ['deleted'],
            ['from_addr_name'],
            ['reply_to_addr'],
            ['to_addrs_names'],
            ['cc_addrs_names'],
            ['bcc_addrs_names'],
            ['raw_source'],
            ['description_html'],
            ['description'],
            ['date_sent'],
            ['message_id'],
            ['message_uid'],
            ['name'],
            ['type'],
            ['status'],
            ['flagged'],
            ['reply_to_status'],
            ['intent'],
            ['mailbox_id'],
            ['state'],
            ['reply_to_id'],
            ['parent_type'],
            ['parent_id'],
            ['outbound_email_id'],
            ['team_id'],
            ['team_set_id'],
            ['tags'],
            ['accounts_from'],
            ['contacts_from'],
            ['email_addresses_from'],
            ['leads_from'],
            ['prospects_from'],
            ['users_from'],
            ['accounts_to'],
            ['contacts_to'],
            ['email_addresses_to'],
            ['leads_to'],
            ['prospects_to'],
            ['users_to'],
            ['accounts_cc'],
            ['contacts_cc'],
            ['email_addresses_cc'],
            ['leads_cc'],
            ['prospects_cc'],
            ['users_cc'],
            ['accounts_bcc'],
            ['contacts_bcc'],
            ['email_addresses_bcc'],
            ['leads_bcc'],
            ['prospects_bcc'],
            ['users_bcc'],
        ];
    }

    /**
     * @covers ::checkAccess
     * @dataProvider checkFieldAccessReadableProvider
     */
    public function testFieldCheckAccess_ReadableFields($field)
    {
        $bean = $this->createMock('\\Email');
        $bean->state = \Email::STATE_ARCHIVED;

        $context = [
            'bean' => $bean,
            'field' => $field,
        ];

        $acl = $this->createPartialMock('\\SugarACLArchivedEmails', ['isWriteOperation']);
        $acl->method('isWriteOperation')->willReturn(false);

        $actual = $acl->checkAccess('Emails', 'field', $context);
        $this->assertTrue($actual);
    }

    public function checkFieldAccessWritableProvider()
    {
        return [
            [
                'id',
                true,
            ],
            [
                'date_entered',
                true,
            ],
            [
                'date_modified',
                true,
            ],
            [
                'assigned_user_id',
                true,
            ],
            [
                'modified_user_id',
                true,
            ],
            [
                'created_by',
                true,
            ],
            [
                'deleted',
                true,
            ],
            [
                'from_addr_name',
                true,
            ],
            [
                'reply_to_addr',
                true,
            ],
            [
                'to_addrs_names',
                true,
            ],
            [
                'cc_addrs_names',
                true,
            ],
            [
                'bcc_addrs_names',
                true,
            ],
            [
                'raw_source',
                false,
            ],
            [
                'description_html',
                false,
            ],
            [
                'description',
                false,
            ],
            [
                'date_sent',
                false,
            ],
            [
                'message_id',
                false,
            ],
            [
                'message_uid',
                false,
            ],
            [
                'name',
                false,
            ],
            [
                'type',
                true,
            ],
            [
                'status',
                true,
            ],
            [
                'flagged',
                true,
            ],
            [
                'reply_to_status',
                true,
            ],
            [
                'intent',
                true,
            ],
            [
                'mailbox_id',
                false,
            ],
            [
                'state',
                false,
            ],
            [
                'reply_to_id',
                false,
            ],
            [
                'parent_type',
                true,
            ],
            [
                'parent_id',
                true,
            ],
            [
                'outbound_email_id',
                false,
            ],
            [
                'team_id',
                true,
            ],
            [
                'team_set_id',
                true,
            ],
            [
                'tags',
                true,
            ],
            [
                'accounts_from',
                false,
            ],
            [
                'contacts_from',
                false,
            ],
            [
                'email_addresses_from',
                false,
            ],
            [
                'leads_from',
                false,
            ],
            [
                'prospects_from',
                false,
            ],
            [
                'users_from',
                false,
            ],
            [
                'accounts_to',
                false,
            ],
            [
                'contacts_to',
                false,
            ],
            [
                'email_addresses_to',
                false,
            ],
            [
                'leads_to',
                false,
            ],
            [
                'prospects_to',
                false,
            ],
            [
                'users_to',
                false,
            ],
            [
                'accounts_cc',
                false,
            ],
            [
                'contacts_cc',
                false,
            ],
            [
                'email_addresses_cc',
                false,
            ],
            [
                'leads_cc',
                false,
            ],
            [
                'prospects_cc',
                false,
            ],
            [
                'users_cc',
                false,
            ],
            [
                'accounts_bcc',
                false,
            ],
            [
                'contacts_bcc',
                false,
            ],
            [
                'email_addresses_bcc',
                false,
            ],
            [
                'leads_bcc',
                false,
            ],
            [
                'prospects_bcc',
                false,
            ],
            [
                'users_bcc',
                false,
            ],
        ];
    }

    /**
     * @covers ::checkAccess
     * @dataProvider checkFieldAccessWritableProvider
     */
    public function testFieldCheckAccess_WritableFields($field, $isWritable)
    {
        $bean = $this->createPartialMock('\\Email', ['isUpdate']);
        $bean->method('isUpdate')->willReturn(true);
        $bean->state = \Email::STATE_ARCHIVED;

        $context = [
            'bean' => $bean,
            'field' => $field,
        ];

        $acl = $this->createPartialMock('\\SugarACLArchivedEmails', ['isWriteOperation', 'getLinkFieldsForCollection']);
        $acl->method('isWriteOperation')->willReturn(true);
        $acl->method('getLinkFieldsForCollection')
            ->withConsecutive(
                [$this->identicalTo($bean), $this->identicalTo('from')],
                [$this->identicalTo($bean), $this->identicalTo('to')],
                [$this->identicalTo($bean), $this->identicalTo('cc')],
                [$this->identicalTo($bean), $this->identicalTo('bcc')]
            )
            ->willReturnOnConsecutiveCalls(
                [
                    'accounts_from',
                    'contacts_from',
                    'email_addresses_from',
                    'leads_from',
                    'prospects_from',
                    'users_from',
                ],
                [
                    'accounts_to',
                    'contacts_to',
                    'email_addresses_to',
                    'leads_to',
                    'prospects_to',
                    'users_to',
                ],
                [
                    'accounts_cc',
                    'contacts_cc',
                    'email_addresses_cc',
                    'leads_cc',
                    'prospects_cc',
                    'users_cc',
                ],
                [
                    'accounts_bcc',
                    'contacts_bcc',
                    'email_addresses_bcc',
                    'leads_bcc',
                    'prospects_bcc',
                    'users_bcc',
                ]
            );

        $actual = $acl->checkAccess('Emails', 'field', $context);
        $this->assertSame($isWritable, $actual);
    }

    /**
     * @covers ::checkAccess
     * @dataProvider checkFieldAccessReadableProvider
     */
    public function testFieldCheckAccess_WritableFieldsWhenEmailIsNotArchived($field)
    {
        $bean = $this->createMock('\\Email');
        $bean->state = \Email::STATE_DRAFT;

        $context = [
            'bean' => $bean,
            'field' => $field,
        ];

        $acl = $this->createPartialMock('\\SugarACLArchivedEmails', ['isWriteOperation']);
        $acl->method('isWriteOperation')->willReturn(true);

        $actual = $acl->checkAccess('Emails', 'field', $context);
        $this->assertTrue($actual);
    }

    public function checkFieldAccessWritableWhenArchivedEmailIsNewProvider()
    {
        return [
            [
                'id',
                true,
            ],
            [
                'date_entered',
                true,
            ],
            [
                'date_modified',
                true,
            ],
            [
                'assigned_user_id',
                true,
            ],
            [
                'modified_user_id',
                true,
            ],
            [
                'created_by',
                true,
            ],
            [
                'deleted',
                true,
            ],
            [
                'from_addr_name',
                true,
            ],
            [
                'reply_to_addr',
                true,
            ],
            [
                'to_addrs_names',
                true,
            ],
            [
                'cc_addrs_names',
                true,
            ],
            [
                'bcc_addrs_names',
                true,
            ],
            [
                'raw_source',
                true,
            ],
            [
                'description_html',
                true,
            ],
            [
                'description',
                true,
            ],
            [
                'date_sent',
                true,
            ],
            [
                'message_id',
                true,
            ],
            [
                'message_uid',
                true,
            ],
            [
                'name',
                true,
            ],
            [
                'type',
                true,
            ],
            [
                'status',
                true,
            ],
            [
                'flagged',
                true,
            ],
            [
                'reply_to_status',
                true,
            ],
            [
                'intent',
                true,
            ],
            [
                'mailbox_id',
                true,
            ],
            [
                'state',
                true,
            ],
            [
                'reply_to_id',
                true,
            ],
            [
                'parent_type',
                true,
            ],
            [
                'parent_id',
                true,
            ],
            [
                'outbound_email_id',
                false,
            ],
            [
                'team_id',
                true,
            ],
            [
                'team_set_id',
                true,
            ],
            [
                'tags',
                true,
            ],
            [
                'accounts_from',
                true,
            ],
            [
                'contacts_from',
                true,
            ],
            [
                'email_addresses_from',
                true,
            ],
            [
                'leads_from',
                true,
            ],
            [
                'prospects_from',
                true,
            ],
            [
                'users_from',
                true,
            ],
            [
                'accounts_to',
                true,
            ],
            [
                'contacts_to',
                true,
            ],
            [
                'email_addresses_to',
                true,
            ],
            [
                'leads_to',
                true,
            ],
            [
                'prospects_to',
                true,
            ],
            [
                'users_to',
                true,
            ],
            [
                'accounts_cc',
                true,
            ],
            [
                'contacts_cc',
                true,
            ],
            [
                'email_addresses_cc',
                true,
            ],
            [
                'leads_cc',
                true,
            ],
            [
                'prospects_cc',
                true,
            ],
            [
                'users_cc',
                true,
            ],
            [
                'accounts_bcc',
                true,
            ],
            [
                'contacts_bcc',
                true,
            ],
            [
                'email_addresses_bcc',
                true,
            ],
            [
                'leads_bcc',
                true,
            ],
            [
                'prospects_bcc',
                true,
            ],
            [
                'users_bcc',
                true,
            ],
        ];
    }

    /**
     * @covers ::checkAccess
     * @dataProvider checkFieldAccessWritableWhenArchivedEmailIsNewProvider
     */
    public function testFieldCheckAccess_WritableFieldsWhenArchivedEmailIsNew($field, $isWritable)
    {
        $bean = $this->createPartialMock('\\Email', ['isUpdate']);
        $bean->method('isUpdate')->willReturn(false);
        $bean->state = \Email::STATE_ARCHIVED;

        $context = [
            'bean' => $bean,
            'field' => $field,
        ];

        $acl = $this->createPartialMock('\\SugarACLArchivedEmails', ['isWriteOperation']);
        $acl->method('isWriteOperation')->willReturn(true);

        $actual = $acl->checkAccess('Emails', 'field', $context);
        $this->assertSame($isWritable, $actual);
    }
}
