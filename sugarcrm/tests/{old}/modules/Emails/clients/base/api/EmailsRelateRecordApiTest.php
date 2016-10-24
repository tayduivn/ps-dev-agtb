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

require_once 'modules/Emails/clients/base/api/EmailsRelateRecordApi.php';

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

    /**
     * @covers ::createRelatedRecord
     */
    public function testCreateRelatedRecord()
    {
        $email = BeanFactory::newBean('Emails');
        $linkName = 'email_addresses_to';
        $addressId = create_guid();
        $args = array(
            'module' => 'Emails',
            'record' => $email->id,
            'link_name' => $linkName,
            'email_address' => 'myname@mydomain.com',
        );

        $link = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(array('getRelatedModuleName'))
            ->getMock();
        $link->expects($this->once())
            ->method('getRelatedModuleName')
            ->willReturn('EmailAddresses');
        $email->$linkName = $link;

        $api = $this->getMockBuilder('EmailsRelateRecordApi')
            ->disableOriginalConstructor()
            ->setMethods(array('loadBean', 'checkRelatedSecurity', 'getEmailAddressId', 'createRelatedLink'))
            ->getMock();
        $api->expects($this->once())
            ->method('loadBean')
            ->willReturn($email);
        $api->expects($this->once())
            ->method('checkRelatedSecurity')
            ->willReturn(array($linkName));
        $api->expects($this->once())
            ->method('getEmailAddressId')
            ->willReturn($addressId);
        $expected = array_merge($args, array('remote_id' => $addressId));
        $api->expects($this->once())
            ->method('createRelatedLink')
            ->with($this->identicalTo($this->service), $this->equalTo($expected));

        $api->createRelatedRecord($this->service, $args);
    }
}
