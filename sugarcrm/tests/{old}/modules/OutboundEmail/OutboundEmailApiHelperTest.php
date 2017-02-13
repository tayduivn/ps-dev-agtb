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
 * @coversDefaultClass OutboundEmailApiHelper
 */
class OutboundEmailApiHelperTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('current_user');
    }

    public function populateFromApiProvider()
    {
        return [
            [
                [
                    'name' => 'system',
                    'type' => 'system',
                    'mail_sendtype' => 'smtp',
                    'mail_smtptype' => 'exchange',
                    'mail_smtpserver' => 'smtp.sugarcrm.com',
                    'mail_smtpuser' => 'foo',
                    'mail_smtppass' => 'bar',
                    'mail_smtpport' => 25,
                    'mail_smtpauth_req' => 1,
                    'mail_smtpssl' => 2,
                ],
                [
                    'user_id' => Uuid::uuid1(),
                    'name' => 'foo',
                    'type' => 'user',
                    'mail_sendtype' => 'web',
                    'mail_smtptype' => 'other',
                    'mail_smtpserver' => 'foo.com',
                    'mail_smtpuser' => 'biz',
                    'mail_smtppass' => 'baz',
                    'mail_smtpport' => 443,
                    'mail_smtpauth_req' => 0,
                    'mail_smtpssl' => 1,
                ],
                [
                    'name' => 'system',
                    'type' => 'system',
                    'mail_sendtype' => 'web',
                    'mail_smtptype' => 'other',
                    'mail_smtpserver' => 'foo.com',
                    'mail_smtpuser' => 'biz',
                    'mail_smtppass' => 'baz',
                    'mail_smtpport' => 443,
                    'mail_smtpauth_req' => 0,
                    'mail_smtpssl' => 1,
                ],
            ],
            [
                [
                    'name' => 'system',
                    'type' => 'system-override',
                    'mail_sendtype' => 'smtp',
                    'mail_smtptype' => 'exchange',
                    'mail_smtpserver' => 'smtp.sugarcrm.com',
                    'mail_smtpuser' => 'foo',
                    'mail_smtppass' => 'bar',
                    'mail_smtpport' => 25,
                    'mail_smtpauth_req' => 1,
                    'mail_smtpssl' => 2,
                ],
                [
                    'user_id' => Uuid::uuid1(),
                    'name' => 'foo',
                    'type' => 'user',
                    'mail_sendtype' => 'web',
                    'mail_smtptype' => 'other',
                    'mail_smtpserver' => 'foo.com',
                    'mail_smtpuser' => 'biz',
                    'mail_smtppass' => 'baz',
                    'mail_smtpport' => 443,
                    'mail_smtpauth_req' => 0,
                    'mail_smtpssl' => 1,
                ],
                [
                    'name' => 'system',
                    'type' => 'system-override',
                    'mail_sendtype' => 'smtp',
                    'mail_smtptype' => 'exchange',
                    'mail_smtpserver' => 'smtp.sugarcrm.com',
                    'mail_smtpuser' => 'biz',
                    'mail_smtppass' => 'baz',
                    'mail_smtpport' => 25,
                    'mail_smtpauth_req' => 1,
                    'mail_smtpssl' => 2,
                ],
            ],
            [
                [
                    'name' => 'foo',
                    'type' => 'user',
                    'mail_sendtype' => 'smtp',
                    'mail_smtptype' => 'exchange',
                    'mail_smtpserver' => 'smtp.sugarcrm.com',
                    'mail_smtpuser' => 'foo',
                    'mail_smtppass' => 'bar',
                    'mail_smtpport' => 25,
                    'mail_smtpauth_req' => 1,
                    'mail_smtpssl' => 2,
                ],
                [
                    'user_id' => Uuid::uuid1(),
                    'name' => 'bar',
                    'type' => 'user',
                    'mail_sendtype' => 'web',
                    'mail_smtptype' => 'other',
                    'mail_smtpserver' => 'foo.com',
                    'mail_smtpuser' => 'biz',
                    'mail_smtppass' => 'baz',
                    'mail_smtpport' => 443,
                    'mail_smtpauth_req' => 0,
                    'mail_smtpssl' => 1,
                ],
                [
                    'name' => 'bar',
                    'type' => 'user',
                    'mail_sendtype' => 'web',
                    'mail_smtptype' => 'other',
                    'mail_smtpserver' => 'foo.com',
                    'mail_smtpuser' => 'biz',
                    'mail_smtppass' => 'baz',
                    'mail_smtpport' => 443,
                    'mail_smtpauth_req' => 0,
                    'mail_smtpssl' => 1,
                ],
            ],
        ];
    }

    /**
     * @covers ::populateFromApi
     * @dataProvider populateFromApiProvider
     */
    public function testPopulateFromApi($initialData, $submittedData, $expectedData)
    {
        $bean = BeanFactory::newBean('OutboundEmail');
        $bean->id = Uuid::uuid1();

        // The owner is not allowed to change.
        $expectedData['user_id'] = $bean->user_id = $GLOBALS['current_user']->id;

        foreach ($initialData as $field => $value) {
            $bean->{$field} = $value;
        }

        $helper = new OutboundEmailApiHelper(SugarTestRestUtilities::getRestServiceMock());
        $helper->populateFromApi($bean, $submittedData);

        foreach ($expectedData as $field => $value) {
            $this->assertSame($value, $bean->{$field}, "{$field} should be {$value}");
        }
    }

    public function formatForApiProvider()
    {
        return [
            [
                [
                    'name' => 'foo',
                    'type' => 'user',
                    'mail_sendtype' => 'smtp',
                    'mail_smtptype' => 'exchange',
                    'mail_smtpserver' => 'smtp.sugarcrm.com',
                    'mail_smtpuser' => 'foo',
                    'mail_smtppass' => 'P@55w0rd',
                    'mail_smtpport' => 25,
                    'mail_smtpauth_req' => 1,
                    'mail_smtpssl' => 2,
                ],
                [
                    'name',
                    'type',
                ],
                [
                    'name' => 'foo',
                    'type' => 'user',
                ],
            ],
            [
                [
                    'name' => 'foo',
                    'type' => 'user',
                    'mail_sendtype' => 'smtp',
                    'mail_smtptype' => 'exchange',
                    'mail_smtpserver' => 'smtp.sugarcrm.com',
                    'mail_smtpuser' => 'foo',
                    'mail_smtppass' => '',
                    'mail_smtpport' => 25,
                    'mail_smtpauth_req' => 1,
                    'mail_smtpssl' => 2,
                ],
                [
                    'name',
                    'type',
                ],
                [
                    'name' => 'foo',
                    'type' => 'user',
                ],
            ],
            [
                [
                    'name' => 'foo',
                    'type' => 'user',
                    'mail_sendtype' => 'smtp',
                    'mail_smtptype' => 'exchange',
                    'mail_smtpserver' => 'smtp.sugarcrm.com',
                    'mail_smtpuser' => 'foo',
                    'mail_smtppass' => 'P@55w0rd',
                    'mail_smtpport' => 25,
                    'mail_smtpauth_req' => 1,
                    'mail_smtpssl' => 2,
                ],
                [
                    'name',
                    'type',
                    'mail_smtpuser',
                    'mail_smtppass',
                ],
                [
                    'name' => 'foo',
                    'type' => 'user',
                    'mail_smtpuser' => 'foo',
                    'mail_smtppass' => true,
                ],
            ],
            [
                [
                    'name' => 'foo',
                    'type' => 'user',
                    'mail_sendtype' => 'smtp',
                    'mail_smtptype' => 'exchange',
                    'mail_smtpserver' => 'smtp.sugarcrm.com',
                    'mail_smtpuser' => 'foo',
                    'mail_smtppass' => '',
                    'mail_smtpport' => 25,
                    'mail_smtpauth_req' => 1,
                    'mail_smtpssl' => 2,
                ],
                [
                    'name',
                    'type',
                    'mail_smtpuser',
                    'mail_smtppass',
                ],
                [
                    'name' => 'foo',
                    'type' => 'user',
                    'mail_smtpuser' => 'foo',
                    'mail_smtppass' => null,
                ],
            ],
        ];
    }

    /**
     * @covers ::formatForApi
     * @dataProvider formatForApiProvider
     */
    public function testFormatForApi($initialData, $fieldList, $expected)
    {
        $bean = BeanFactory::newBean('OutboundEmail');
        $bean->id = Uuid::uuid1();
        $bean->user_id = $GLOBALS['current_user']->id;

        foreach ($initialData as $field => $value) {
            $bean->{$field} = $value;
        }

        $helper = new OutboundEmailApiHelper(SugarTestRestUtilities::getRestServiceMock());
        $actual = $helper->formatForApi($bean, $fieldList);

        // Testing for these attributes is unnecessary.
        unset($actual['_acl']);
        unset($actual['locked_fields']);

        $this->assertEquals($expected, $actual);
    }
}
