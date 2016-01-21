<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTests\modules\CarrierSugar;

require_once 'modules/CarrierSugar/Transport.php';

use CarrierSugarTransport;
use Notifications;

/**
 * Class CarrierSugarTransportTest

 * @coversDefaultClass \CarrierSugarTransport
 */
class CarrierSugarTransportTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var CarrierSugarTransport */
    protected $transport = null;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        \BeanFactory::setBeanClass('Notifications', 'Sugarcrm\SugarcrmTests\modules\CarrierSugar\NotificationsCRYS1268');
        $this->transport = new CarrierSugarTransport();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        NotificationsCRYS1268::$saveReturn = null;
        NotificationsCRYS1268::$saveData = array();
        \BeanFactory::setBeanClass('Notifications');
        parent::tearDown();
    }

    /**
     * Data provider for testSend
     *
     * @see CarrierSugarTransportTest::testSend
     * @return array
     */
    public static function sendProvider()
    {
        $rand = rand(1000, 9999);

        return array(
            'returnsFalseOnEmptyMessage' => array(
                'recipient' => 'guid-' . $rand,
                'message' => array(),
                'actualSaveReturn' => true,
                'expectedResult' => false,
                'expectedData' => array(),
            ),
            'returnsFalseOnNotOurMessage' => array(
                'recipient' => 'guid-' . $rand,
                'message' => array(
                    'someField' => 'someValue ' . $rand,
                ),
                'actualSaveReturn' => true,
                'expectedResult' => false,
                'expectedData' => array(),
            ),
            'returnsFalseOnValidMessageButInvalidSave' => array(
                'recipient' => 'guid-' . $rand,
                'message' => array(
                    'title' => 'someValue ' . $rand,
                ),
                'actualSaveReturn' => false,
                'expectedResult' => false,
                'expectedData' => array(),
            ),
            'validWhenTitleIsPresent' => array(
                'recipient' => 'guid-' . $rand,
                'message' => array(
                    'title' => 'title ' . $rand,
                ),
                'actualSaveReturn' => true,
                'expectedResult' => true,
                'expectedData' => array(
                    'name' => 'title ' . $rand,
                ),
            ),
            'validWhenHtmlIsPresent' => array(
                'recipient' => 'guid-' . $rand,
                'message' => array(
                    'html' => 'html ' . $rand,
                ),
                'actualSaveReturn' => true,
                'expectedResult' => true,
                'expectedData' => array(
                    'description' => 'html ' . $rand,
                ),
            ),
            'validWhenTextIsPresent' => array(
                'recipient' => 'guid-' . $rand,
                'message' => array(
                    'text' => 'text ' . $rand,
                ),
                'actualSaveReturn' => true,
                'expectedResult' => true,
                'expectedData' => array(
                    'description' => 'text ' . $rand,
                ),
            ),
            'validWhenHtmlWithTagsIsPresent' => array(
                'recipient' => 'guid-' . $rand,
                'message' => array(
                    'html' => 'html<br> ' . $rand,
                ),
                'actualSaveReturn' => true,
                'expectedResult' => true,
                'expectedData' => array(
                    'description' => 'html<br> ' . $rand,
                ),
            ),
            'validWhenTextWithHtmlSymbolsIsPresent' => array(
                'recipient' => 'guid-' . $rand,
                'message' => array(
                    'text' => 'text > ' . $rand,
                ),
                'actualSaveReturn' => true,
                'expectedResult' => true,
                'expectedData' => array(
                    'description' => 'text &gt; ' . $rand,
                ),
            ),
            'validWhenHtmlAndTextArePresentThenTextIsIgnored' => array(
                'recipient' => 'guid-' . $rand,
                'message' => array(
                    'html' => 'html ' . $rand,
                    'text' => 'text > ' . $rand,
                ),
                'actualSaveReturn' => true,
                'expectedResult' => true,
                'expectedData' => array(
                    'description' => 'html ' . $rand,
                ),
            ),
            'validOnRichMessage' => array(
                'recipient' => 'guid-' . $rand,
                'message' => array(
                    'title' => 'title ' . $rand,
                    'html' => 'html ' . $rand,
                    'text' => 'text > ' . $rand,
                ),
                'actualSaveReturn' => true,
                'expectedResult' => true,
                'expectedData' => array(
                    'name' => 'title ' . $rand,
                    'description' => 'html ' . $rand,
                ),
            ),
        );
    }

    /**
     * Testing notification generation
     *
     * @covers CarrierSugarTransport::send
     * @dataProvider sendProvider
     * @param string $recipient
     * @param array $message
     * @param bool $actualSaveReturn
     * @param bool $expectedResult
     * @param array $expectedData
     */
    public function testSend($recipient, $message, $actualSaveReturn, $expectedResult, $expectedData)
    {
        NotificationsCRYS1268::$saveReturn = $actualSaveReturn;
        $result = $this->transport->send($recipient, $message);
        $this->assertEquals($expectedResult, $result);
        $this->assertArraySubset($expectedData, NotificationsCRYS1268::$saveData);
    }
}

/**
 * Mocking save method
 *
 * @package Sugarcrm\SugarcrmTests\modules\CarrierSugar
 */
class NotificationsCRYS1268 extends Notifications
{
    /** @var mixed */
    public static $saveReturn = null;

    /** @var array */
    public static $saveData = array();

    /**
     * @inheritDoc
     */
    function save($check_notify = false)
    {
        static::$saveData = $this->toArray();
        return static::$saveReturn;
    }
}
