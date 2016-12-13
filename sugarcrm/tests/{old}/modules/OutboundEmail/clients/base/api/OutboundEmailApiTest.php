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

require_once 'modules/OutboundEmail/clients/base/api/OutboundEmailApi.php';

use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @coversDefaultClass OutboundEmailApi
 * @group api
 * @group email
 */
class OutboundEmailApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $api;
    private $service;

    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');
        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new OutboundEmailApi();
    }

    protected function tearDown()
    {
        BeanFactory::setBeanClass('OutboundEmail');  // Must be Reset
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function createSystemRecordDataProvider()
    {
        return [
            ['system'],
            ['system-override'],
        ];
    }

    /**
     * @covers ::createRecord
     * @dataProvider createSystemRecordDataProvider
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testCreateSystemRecord($type)
    {
        $args = [
            'module' => 'OutboundEmail',
            'type' => $type,
        ];
        $response = $this->api->createRecord($this->service, $args);
    }

    /**
     * @covers ::createRecord
     */
    public function testCreateRecord_TypeUser_OK()
    {
        $outboundEmailMock = $this->getMockBuilder('OutboundEmail')
            ->setMethods(['save'])
            ->getMock();

        BeanFactory::setBeanClass('OutboundEmail', get_class($outboundEmailMock));

        $mockMailer = $this->getMockBuilder('SmtpMailer')
            ->disableOriginalConstructor()
            ->setMethods(['connect'])
            ->getMock();

        $mockMailer->expects($this->once())
            ->method('connect');

        $outboundEmailApiMock = $this->getMockBuilder('OutboundEmailApi')
            ->setMethods(['getMailer'])
            ->getMock();

        $outboundEmailApiMock->expects($this->once())
            ->method('getMailer')
            ->will($this->returnValue($mockMailer));

        $args = [
            'module' => 'OutboundEmail',
            'mail_smtpserver' => 'smtp.x.y',
            'mail_smtpport' => 465,
        ];

        $outboundEmailApiMock->createRecord($this->service, $args);
    }

    /**
     * @covers ::createRecord
     * @expectedException SugarApiException
     */
    public function testCreateRecord_TypeUser_InvalidConnection_ThrowsException()
    {
        $mockMailer = $this->getMockBuilder('SmtpMailer')
            ->disableOriginalConstructor()
            ->setMethods(['connect'])
            ->getMock();
        $mockMailer->method('connect')->willThrowException(new MailerException());

        $outboundEmailApiMock = $this->getMockBuilder('OutboundEmailApi')
            ->setMethods(['getMailer'])
            ->getMock();

        $outboundEmailApiMock->expects($this->once())
            ->method('getMailer')
            ->will($this->returnValue($mockMailer));

        $args = [
            'module' => 'OutboundEmail',
            'mail_smtpserver' => 'smtp.a.b',
            'mail_smtpport' => 465,
        ];

        $outboundEmailApiMock->createRecord($this->service, $args);
    }

    public function updateRecordProvider()
    {
        return [
            ['system', 'system', 1, 0],
            ['system', 'system-override', 0, 1],
            ['foo', 'user', 0, 1],
        ];
    }

    /**
     * @covers ::updateRecord
     * @covers ::saveBean
     * @dataProvider updateRecordProvider
     */
    public function testUpdateRecord($name, $type, $saveSystemCallCount, $saveCallCount)
    {
        $mockMailer = $this->getMockBuilder('SmtpMailer')
            ->disableOriginalConstructor()
            ->setMethods(['connect'])
            ->getMock();

        $mockMailer->expects($this->once())
            ->method('connect');

        $outboundEmailApiMock = $this->getMockBuilder('OutboundEmailApi')
            ->setMethods(['getMailer'])
            ->getMock();

        $outboundEmailApiMock->expects($this->once())
            ->method('getMailer')
            ->will($this->returnValue($mockMailer));

        $oe = $this->getMockBuilder('OutboundEmail')
            ->setMethods(['saveSystem', 'save'])
            ->getMock();
        $oe->expects($this->exactly($saveSystemCallCount))->method('saveSystem');
        $oe->expects($this->exactly($saveCallCount))->method('save');

        $oe->id = Uuid::uuid1();
        $oe->name = $name;
        $oe->type = $type;
        $oe->user_id = $GLOBALS['current_user']->id;
        $oe->mail_smtpport = 25;
        BeanFactory::registerBean($oe);

        $args = [
            'module' => 'OutboundEmail',
            'record' => $oe->id,
            'mail_smtpport' => 465,
        ];
        $response = $outboundEmailApiMock->updateRecord($this->service, $args);

        BeanFactory::unregisterBean($oe);
    }
}
