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
 * @coversDefaultClass EmailsRelateRecordApi
 * @group api
 * @group email
 */
class EmailsRelateRecordApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $service;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    protected function setUp()
    {
        parent::setUp();
        $this->service = SugarTestRestUtilities::getRestServiceMock();
    }

    protected function tearDown()
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        parent::tearDown();
    }

    public function createRelatedRecordCannotCreateSendersAndRecipientsProvider()
    {
        return [
            ['accounts_from'],
            ['accounts_to'],
            ['accounts_cc'],
            ['accounts_bcc'],
            ['contacts_from'],
            ['contacts_to'],
            ['contacts_cc'],
            ['contacts_bcc'],
            ['leads_from'],
            ['leads_to'],
            ['leads_cc'],
            ['leads_bcc'],
            ['prospects_from'],
            ['prospects_to'],
            ['prospects_cc'],
            ['prospects_bcc'],
            ['users_from'],
            ['users_to'],
            ['users_cc'],
            ['users_bcc'],
        ];
    }

    /**
     * Cannot create new Accounts, Contacts, Leads, Prospects, or Users when linking senders or recipients. Only
     * existing records from those modules can be linked.
     *
     * @covers ::createRelatedRecord
     * @dataProvider createRelatedRecordCannotCreateSendersAndRecipientsProvider
     * @expectedException SugarApiExceptionNotAuthorized
     * @param string $linkName
     */
    public function testCreateRelatedRecord_CannotCreateSendersAndRecipients($linkName)
    {
        // Need the current user to be an admin to test the users_* links.
        $GLOBALS['current_user']->is_admin = 1;

        $email = BeanFactory::newBean('Emails');
        $email->id = Uuid::uuid1();

        $api = $this->createPartialMock('EmailsRelateRecordApi', ['loadBean']);
        $api->expects($this->once())->method('loadBean')->willReturn($email);

        $args = [
            'module' => 'Emails',
            'record' => $email->id,
            'link_name' => $linkName,
            // Plus some data for the new record.
        ];
        $api->createRelatedRecord($this->service, $args);
    }

    /**
     * @covers ::createRelatedRecord
     */
    public function testCreateRelatedRecord_LinkToAnExistingEmailAddress()
    {
        $email = BeanFactory::newBean('Emails');
        $email->id = Uuid::uuid1();
        $linkName = 'email_addresses_to';
        $addressId = Uuid::uuid1();
        $args = [
            'module' => 'Emails',
            'record' => $email->id,
            'link_name' => $linkName,
            'email_address' => 'myname@mydomain.com',
        ];

        $link = $this->createPartialMock('Link2', ['getRelatedModuleName']);
        $link->expects($this->once())->method('getRelatedModuleName')->willReturn('EmailAddresses');
        $email->$linkName = $link;

        $api = $this->createPartialMock('EmailsRelateRecordApi', [
            'loadBean',
            'checkRelatedSecurity',
            'getEmailAddressId',
            'createRelatedLink',
        ]);
        $api->expects($this->once())->method('loadBean')->willReturn($email);
        $api->expects($this->once())->method('checkRelatedSecurity')->willReturn([$linkName]);
        $api->expects($this->once())->method('getEmailAddressId')->willReturn($addressId);
        $expected = array_merge($args, ['remote_id' => $addressId]);
        $api->expects($this->once())
            ->method('createRelatedLink')
            ->with($this->identicalTo($this->service), $this->equalTo($expected));

        $api->createRelatedRecord($this->service, $args);
    }

    public function deleteRelatedLinkProvider()
    {
        return [
            ['accounts_from'],
            ['contacts_from'],
            ['email_addresses_from'],
            ['leads_from'],
            ['prospects_from'],
            ['users_from'],
        ];
    }

    /**
     * Cannot break the link between an email and its sender.
     *
     * @covers ::deleteRelatedLink
     * @dataProvider deleteRelatedLinkProvider
     * @expectedException SugarApiExceptionNotAuthorized
     * @param string $linkName
     */
    public function testDeleteRelatedLink($linkName)
    {
        $args = [
            'module' => 'Emails',
            'record' => Uuid::uuid1(),
            'link_name' => $linkName,
            'remote_id' => Uuid::uuid1(),
        ];
        $api = new EmailsRelateRecordApi();
        $api->deleteRelatedLink($this->service, $args);
    }
}
