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

require_once 'include/OutboundEmail/OutboundEmail.php';
require_once 'tests/{old}/modules/OutboundEmailConfiguration/OutboundEmailConfigurationTestHelper.php';

use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @coversDefaultClass OutboundEmail
 */
class OutboundEmailTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');
        OutboundEmailConfigurationTestHelper::setUp();
    }

    protected function tearDown()
    {
        OutboundEmailConfigurationTestHelper::tearDown();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @covers ::getOwnerField
     */
    public function testGetOwnerField()
    {
        $bean = BeanFactory::newBean('OutboundEmail');
        $ownerField = $bean->getOwnerField();
        $this->assertSame('user_id', $ownerField);
    }

    /**
     * @covers ::isOwner
     */
    public function testIsOwner()
    {
        $bean = BeanFactory::newBean('OutboundEmail');

        $isOwner = $bean->isOwner($GLOBALS['current_user']->id);
        $this->assertTrue($isOwner, 'Should be true for the current user when creating a new record');

        $isOwner = $bean->isOwner(Uuid::uuid1());
        $this->assertFalse($isOwner, 'Should be false a non-current user when creating a new record');

        $bean->id = Uuid::uuid1();
        $bean->new_with_id = false;
        $bean->user_id = $GLOBALS['current_user']->id;

        $isOwner = $bean->isOwner($GLOBALS['current_user']->id);
        $this->assertTrue($isOwner, 'Should be true for the owner of an existing record');

        $isOwner = $bean->isOwner(Uuid::uuid1());
        $this->assertFalse($isOwner, 'Should be false for the non-owner of an existing record');
    }

    /**
     * @covers ::save
     */
    public function testSave()
    {
        $bean = BeanFactory::newBean('OutboundEmail');
        $bean->id = Uuid::uuid1();
        $bean->new_with_id = true;
        $bean->name = 'test outbound account';
        $bean->mail_smtpserver = 'smtp.sugarcrm.com';
        $bean->mail_smtpuser = 'sugarcrm';
        $bean->mail_smtppass = 'foobar';
        $bean->email_address_id = Uuid::uuid1();
        $id = $bean->save();

        $this->assertSame($bean->id, $id, 'OutboundEmail save returned abnormally');

        $userId = $GLOBALS['db']->getOne("SELECT user_id FROM outbound_email WHERE id='{$bean->id}'");
        $this->assertSame($GLOBALS['current_user']->id, $userId, 'Should be owned by the current user');
    }

    /**
     * @covers ::mark_deleted
     */
    public function testMarkDeleted()
    {
        $bean = $this->createPartialMock('OutboundEmail', ['delete']);
        $bean->method('delete')->willReturn(true);
        $bean->id = Uuid::uuid1();

        $actual = $bean->mark_deleted(Uuid::uuid1());
        $this->assertFalse($actual, 'Should return false when trying to delete a different instance');

        $actual = $bean->mark_deleted($bean->id);
        $this->assertTrue($actual, 'Should return true when deleting the instance');
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $bean = BeanFactory::newBean('OutboundEmail');

        $actual = $bean->delete();
        $this->assertFalse($actual, 'Should return false when trying to delete an instance without an ID');

        // Now create a record that can be deleted.
        $bean->name = 'test outbound account';
        $bean->mail_smtpserver = 'smtp.sugarcrm.com';
        $bean->mail_smtpuser = 'sugarcrm';
        $bean->mail_smtppass = 'foobar';
        $bean->save();

        $actual = $bean->delete();
        $this->assertTrue($actual, 'Should return true when deleting the instance');

        $actual = $GLOBALS['db']->getOne("SELECT COUNT(id) FROM outbound_email WHERE id='{$bean->id}'");
        $this->assertEquals(0, $actual, 'Should have deleted the specified row');
    }

    /**
     * @covers ::populateDefaultValues
     */
    public function testPopulateDefaultValues()
    {
        $bean = BeanFactory::newBean('OutboundEmail');
        $bean->populateDefaultValues();

        $emailAddress = $GLOBALS['current_user']->emailAddress->getPrimaryAddress($GLOBALS['current_user']);
        $emailAddressId = $GLOBALS['current_user']->emailAddress->getGuid($emailAddress);

        $this->assertSame($GLOBALS['current_user']->name, $bean->name, 'The names should match');
        $this->assertSame($emailAddress, $bean->email_address, 'The email addresses should match');
        $this->assertSame($emailAddressId, $bean->email_address_id, 'The email address IDs should match');
    }
}
