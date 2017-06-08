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
 * @coversDefaultClass EmailParticipant
 */
class EmailParticipantTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown()
    {
        // Clean up any dangling beans that need to be resaved.
        SugarRelationship::resaveRelatedBeans(false);

        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestEmailUtilities::removeAllCreatedEmails();
        parent::tearDown();
    }

    public function saveEmailTextProvider()
    {
        return [
            'do_not_save_email_text' => [
                false,
                0,
            ],
            'save_email_text' => [
                true,
                1,
            ],
        ];
    }

    /**
     * @dataProvider saveEmailTextProvider
     * @covers ::save
     */
    public function testSave($isUpdate, $callCount)
    {
        $email = $this->createPartialMock('Email', ['isUpdate', 'saveEmailText']);
        $email->method('isUpdate')->willReturn($isUpdate);
        $email->expects($this->exactly($callCount))->method('saveEmailText');
        $email->id = Uuid::uuid1();
        BeanFactory::registerBean($email);
        SugarTestEmailUtilities::setCreatedEmail($email->id);

        $ep = BeanFactory::newBean('EmailParticipants');
        $ep->email_id = $email->id;
        $ep->save();

        BeanFactory::unregisterBean($email);
    }

    /**
     * @covers ::mark_deleted
     * @covers ::mark_relationships_deleted
     * @covers ::delete_linked
     * @covers Link2::delete
     * @covers EmailRecipientRelationship::removeAll
     * @covers EmailRecipientRelationship::remove
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     */
    public function testMarkDeleted()
    {
        $email = SugarTestEmailUtilities::createEmail();
        $email->load_relationship('to_link');

        $contact = SugarTestContactUtilities::createContact();

        $ep = BeanFactory::newBean('EmailParticipants');
        $ep->new_with_id = true;
        $ep->id = Uuid::uuid1();
        $ep->parent_type = $contact->getModuleName();
        $ep->parent_id = $contact->id;
        BeanFactory::registerBean($ep);

        $email->to_link->add($ep);
        SugarRelationship::resaveRelatedBeans();

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$contact->email1}>", $email->to_addrs_names);

        $ep->mark_deleted($ep->id);

        $email->retrieveEmailText();
        $this->assertEmpty($email->to_addrs_names);
    }
}
