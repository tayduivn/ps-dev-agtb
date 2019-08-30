<?php
// FILE SUGARCRM flav=ent ONLY
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
use PHPUnit\Framework\TestCase;

/**
 *  Prepare Register Contact Api.
 */
class RegisterContactApiTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, false]);
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    public function testCreateContact()
    {
        $api = new RegisterContactApi();
        $rest = SugarTestRestUtilities::getRestServiceMock();
        $result = $api->createContactRecord(
            $rest,
            [
                'last_name' => 'lastName',
                'portal_user_company_name' => 'companyName',
                'email' => [
                    0 => ['email_address' => 'a@s.com'],
                ],
                'portal_name' => 'portalName',
                'portal_password' => 'Password123',
            ]
        );
        $this->assertNotEmpty($result);
        $bean = BeanFactory::getBean('Contacts', $result);
        $this->assertEquals('portalName', $bean->portal_name);
        $this->assertEquals(0, $bean->portal_active);
        $this->assertEquals('external', $bean->entry_source);
        $bean->mark_deleted($bean->id);
    }
}
