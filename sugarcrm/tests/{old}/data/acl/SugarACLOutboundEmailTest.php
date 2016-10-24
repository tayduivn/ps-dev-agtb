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
 * @coversDefaultClass SugarACLOutboundEmail
 */
class SugarACLOutboundEmailTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('current_user');
    }

    public function checkModuleAccessProvider()
    {
        return [
            [
                'access',
                'All users should have module access',
            ],
            [
                'team_security',
                'Allow other modules to decide',
            ],
            [
                'list',
                'All users should have access when no bean is in context',
            ],
        ];
    }

    /**
     * @covers ::checkAccess
     * @dataProvider checkModuleAccessProvider
     */
    public function testCheckModuleAccess($view, $message)
    {
        $acl = new SugarACLOutboundEmail();
        $actual = $acl->checkAccess('OutboundEmail', $view, []);
        $this->assertTrue($actual, $message);
    }

    public function checkBeanAccessProvider()
    {
        return [
            [
                ['view', 'list'],
                ['user', 'system-override', 'system'],
                true,
                false,
                true,
                'Users should have read access to records they own',
            ],
            [
                ['edit'],
                ['user', 'system-override', 'system'],
                true,
                false,
                true,
                'Users should have write access to records they own',
            ],
            [
                ['view', 'list'],
                ['user', 'system-override', 'system'],
                false,
                false,
                false,
                'Users should not have read access to records they do not own',
            ],
            [
                ['edit', 'delete'],
                ['user', 'system-override', 'system'],
                false,
                false,
                false,
                'Users should not have write access to records they do not own',
            ],
            [
                ['delete'],
                ['user'],
                true,
                false,
                true,
                'Users should have delete access to user records they own',
            ],
            [
                ['delete'],
                ['system-override', 'system'],
                true,
                false,
                false,
                'Users should not have delete access to the system and system-override records (not even the admin)',
            ],
            [
                ['view', 'list'],
                ['system'],
                true,
                true,
                true,
                'Users should have read access to the system record when allowed',
            ],
        ];
    }

    /**
     * @covers ::checkAccess
     * @dataProvider checkBeanAccessProvider
     */
    public function testCheckBeanAccess($views, $types, $isOwner, $isAllowed, $expected, $message)
    {
        $acl = new SugarACLOutboundEmail();

        $bean = $this->getMockBuilder('OutboundEmail')
            ->setMethods(['isOwner', 'isAllowUserAccessToSystemDefaultOutbound'])
            ->getMock();
        $bean->method('isOwner')->willReturn($isOwner);
        $bean->method('isAllowUserAccessToSystemDefaultOutbound')->willReturn($isAllowed);

        foreach ($views as $view) {
            foreach ($types as $type) {
                $bean->type = $type;
                $actual = $acl->checkAccess('OutboundEmail', $view, ['bean' => $bean]);
                $this->assertSame($expected, $actual, "{$message}: view={$view}, type={$type}");
            }
        }
    }

    public function checkFieldAccessProvider()
    {
        return [
            [
                [
                    'id',
                    'name',
                    'type',
                    'user_id',
                    'mail_sendtype',
                    'mail_smtptype',
                    'mail_smtpserver',
                    'mail_smtpport',
                    'mail_smtpuser',
                    'mail_smtppass',
                    'mail_smtpauth_req',
                    'mail_smtpssl',
                    'deleted',
                ],
                ['view', 'list'],
                ['user', 'system-override', 'system'],
                false,
                true,
                'Users should have read access to all fields for any record they can access',
            ],
            [
                [
                    'id',
                    'name',
                    'type',
                    'user_id',
                    'mail_sendtype',
                    'mail_smtptype',
                    'mail_smtpserver',
                    'mail_smtpport',
                    'mail_smtpuser',
                    'mail_smtppass',
                    'mail_smtpauth_req',
                    'mail_smtpssl',
                    'deleted',
                ],
                ['edit'],
                ['user', 'system-override', 'system'],
                false,
                false,
                'Users should not have write access to any fields for a record they do not own',
            ],
            [
                [
                    // Note: `id` is a readonly field, but it must be editable for the user to update a record.
                    'id',
                    'name',
                    // Note: `type` and `user_id` are readonly fields, but they must be editable for the user to create
                    // a record.
                    'type',
                    'user_id',
                    'mail_sendtype',
                    'mail_smtptype',
                    'mail_smtpserver',
                    'mail_smtpport',
                    'mail_smtpuser',
                    'mail_smtppass',
                    'mail_smtpauth_req',
                    'mail_smtpssl',
                    // Note: `deleted` is not used, but it must be editable for the user to create a record, as it is
                    // required for all beans.
                    'deleted',
                ],
                ['edit'],
                ['user'],
                true,
                true,
                'Users should have write access to all fields for any user record they own',
            ],
            [
                [
                    // Note: `id` is a readonly field, but it must be editable for the user to update a record.
                    'id',
                    'mail_smtpuser',
                    'mail_smtppass',
                ],
                ['edit'],
                ['system-override'],
                true,
                true,
                'Users should have write access to only id, mail_smtpuser, and mail_smtppass for their ' .
                'system-override record',
            ],
            [
                [
                    'name',
                    'mail_sendtype',
                    'mail_smtptype',
                    'mail_smtpserver',
                    'mail_smtpport',
                    'mail_smtpauth_req',
                    'mail_smtpssl',
                ],
                ['edit'],
                ['system-override'],
                true,
                false,
                'Users should not have write access to fields other than id, mail_smtpuser, and mail_smtppass for ' .
                'their system-override record',
            ],
            [
                [
                    // Note: `id` is a readonly field, but it must be editable for the user to update a record.
                    'id',
                    'mail_sendtype',
                    'mail_smtptype',
                    'mail_smtpserver',
                    'mail_smtpport',
                    'mail_smtpuser',
                    'mail_smtppass',
                    'mail_smtpauth_req',
                    'mail_smtpssl',
                ],
                ['edit'],
                ['system'],
                true,
                true,
                'The admin should have write access to all fields except name for the system record',
            ],
            [
                ['name'],
                ['edit'],
                ['system'],
                true,
                false,
                'The admin should not have write access to the name field for the system record',
            ],
        ];
    }

    /**
     * @covers ::checkAccess
     * @dataProvider checkFieldAccessProvider
     */
    public function testCheckFieldAccess($fields, $actions, $types, $isOwner, $expected, $message)
    {
        $acl = new SugarACLOutboundEmail();

        $bean = $this->getMockBuilder('OutboundEmail')
            ->setMethods(['isOwner'])
            ->getMock();
        $bean->method('isOwner')->willReturn($isOwner);

        foreach ($actions as $action) {
            foreach ($types as $type) {
                $bean->type = $type;
                $context = [
                    'action' => $action,
                    'bean' => $bean,
                ];

                foreach ($fields as $field) {
                    $context['field'] = $field;
                    $actual = $acl->checkAccess('OutboundEmail', 'field', $context);
                    $this->assertSame($expected, $actual, "{$message}: field={$field}, action={$action}");
                }
            }
        }
    }
}
