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
    private static $createdIds = [];

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        $sql = "DELETE FROM outbound_email WHERE id IN ('" . implode("','", static::$createdIds) . "')";
        DBManagerFactory::getInstance()->query($sql);

        parent::tearDownAfterClass();
    }

    protected function setUp()
    {
        parent::setUp();
        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new OutboundEmailApi();
    }

    public function createRecordForTypeSystemOrSystemOverrideProvider()
    {
        return [
            ['system'],
            ['system-override'],
        ];
    }

    /**
     * @covers ::createRecord
     * @dataProvider createRecordForTypeSystemOrSystemOverrideProvider
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testCreateRecord_TypeIsSystemOrSystemOverride($type)
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
    public function testCreateRecord_TypeIsUser()
    {
        $mailer = $this->createPartialMock('SmtpMailer', ['connect']);
        $mailer->expects($this->once())->method('connect');

        $api = $this->createPartialMock('OutboundEmailApi', ['getMailer']);
        $api->method('getMailer')->willReturn($mailer);

        $args = [
            'module' => 'OutboundEmail',
            'mail_smtpserver' => 'smtp.x.y',
            'mail_smtpport' => 465,
        ];
        $response = $api->createRecord($this->service, $args);

        $this->assertNotEmpty($response['id'], 'The record should have an ID');
        $this->assertSame($args['mail_smtpserver'], $response['mail_smtpserver'], 'Incorrect mail_smtpserver');
        $this->assertSame($args['mail_smtpport'], $response['mail_smtpport'], 'Incorrect mail_smtpport');
        $this->assertSame($GLOBALS['current_user']->id, $response['user_id'], 'The current user should own the record');

        static::$createdIds[] = $response['id'];
    }

    /**
     * @covers ::createRecord
     * @expectedException SugarApiException
     */
    public function testCreateRecord_TypeIsUser_ConnectionFails()
    {
        $mailer = $this->createPartialMock('SmtpMailer', ['connect']);
        $mailer->method('connect')->willThrowException(new MailerException());

        $api = $this->createPartialMock('OutboundEmailApi', ['getMailer']);
        $api->method('getMailer')->willReturn($mailer);

        $args = [
            'module' => 'OutboundEmail',
            'mail_smtpserver' => 'smtp.a.b',
            'mail_smtpport' => 465,
        ];
        $response = $api->createRecord($this->service, $args);
    }

    public function updateRecordProvider()
    {
        return [
            ['system', 1, 0],
            ['system-override', 0, 1],
            ['user', 0, 1],
        ];
    }

    /**
     * Tests that the correct save method is called depending on the type of record.
     *
     * @covers ::updateRecord
     * @covers ::saveBean
     * @dataProvider updateRecordProvider
     */
    public function testUpdateRecord($type, $saveSystemCallCount, $saveCallCount)
    {
        $oe = $this->getMockBuilder('OutboundEmail')
            ->setMethods(['saveSystem', 'save'])
            ->getMock();
        $oe->expects($this->exactly($saveSystemCallCount))->method('saveSystem')->with($this->equalTo(true));
        $oe->expects($this->exactly($saveCallCount))->method('save');

        $oe->id = Uuid::uuid1();
        $oe->type = $type;
        $oe->user_id = $GLOBALS['current_user']->id;
        $oe->mail_smtpport = 25;
        BeanFactory::registerBean($oe);

        $mailer = $this->createPartialMock('SmtpMailer', ['connect']);
        $mailer->expects($this->once())->method('connect');

        $api = $this->createPartialMock('OutboundEmailApi', ['getMailer', 'reloadBean']);
        $api->method('getMailer')->willReturn($mailer);
        // Avoids the strict retrieve without cache through BeanFactory that hits the database and results in errors
        // due to the record not really being saved.
        $api->method('reloadBean')->willReturn($oe);

        $args = [
            'module' => 'OutboundEmail',
            'record' => $oe->id,
            'mail_smtpport' => 465,
        ];
        $response = $api->updateRecord($this->service, $args);

        BeanFactory::unregisterBean($oe);
    }
}
