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
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function createRecordDataProvider()
    {
        return [
            ['system'],
            ['system-override'],
        ];
    }

    /**
     * @covers ::createRecord
     * @dataProvider createRecordDataProvider
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testCreateRecord($type)
    {
        $args = [
            'module' => 'OutboundEmail',
            'type' => $type,
        ];
        $response = $this->api->createRecord($this->service, $args);
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
        $response = $this->api->updateRecord($this->service, $args);

        BeanFactory::unregisterBean($oe);
    }
}
