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

namespace Sugarcrm\SugarcrmTests\Notification\Emitter\Reminder;

use Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\MessageBuilder as ReminderMessageBuilder;
use Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event as ReminderEvent;

/**
 * Class MessageBuilderTest
 *
 * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\MessageBuilder
 */
class MessageBuilderTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var ReminderMessageBuilder */
    protected $builder;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->builder = new ReminderMessageBuilder();
    }

    /**
     * Data provider for testBuildWithDifferentMessageSignatures.
     *
     * @see MessageBuilderTest::testBuildWithDifferentMessageSignatures
     * @return array
     */
    public static function buildWithDifferentMessageSignaturesProvider()
    {
        return array(
            'withoutSignatureGeneratesNothing' => array(
                'messageSignature' => array(),
                'expectedKeys' => array(),
            ),
            'getsTitleAndGeneratesTitle' => array(
                'messageSignature' => array(
                    'title' => '',
                ),
                'expectedKeys' => array(
                    'title',
                ),
            ),
            'getsTextAndGeneratesText' => array(
                'messageSignature' => array(
                    'text' => '',
                ),
                'expectedKeys' => array(
                    'text',
                ),
            ),
            'getsHtmlAndGeneratesHtml' => array(
                'messageSignature' => array(
                    'html' => '',
                ),
                'expectedKeys' => array(
                    'html',
                ),
            ),
            'getTitleTextAndGeneratesTitleText' => array(
                'messageSignature' => array(
                    'title' => '',
                    'text' => '',
                ),
                'expectedKeys' => array(
                    'title',
                    'text',
                ),
            ),
            'getsTitleHtmlAndGeneratesTitleHtml' => array(
                'messageSignature' => array(
                    'title' => '',
                    'html' => '',
                ),
                'expectedKeys' => array(
                    'title',
                    'html',
                ),
            ),
            'getTextHtmlAndGeneratesTextHtml' => array(
                'messageSignature' => array(
                    'text' => '',
                    'html' => '',
                ),
                'expectedKeys' => array(
                    'text',
                    'html',
                ),
            ),
            'getsTitleTextHtmlAndGeneratesTitleTextHtml' => array(
                'messageSignature' => array(
                    'title' => '',
                    'text' => '',
                    'html' => '',
                ),
                'expectedKeys' => array(
                    'title',
                    'text',
                    'html',
                ),
            ),
        );
    }

    /**
     * Test build method with different message signatures.
     *
     * @dataProvider buildWithDifferentMessageSignaturesProvider
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\MessageBuilder::build
     * @param array $messageSignature Message signature to test.
     * @param array $expectedKeys Expected array messages keys.
     */
    public function testBuildWithDifferentMessageSignatures($messageSignature, $expectedKeys)
    {
        $timeDate = new \TimeDate();
        /** @var \Call|\PHPUnit_Framework_MockObject_MockObject $call */
        $call = $this->getMock('Call');
        $call->id = create_guid();
        $call->date_start = (new \DateTime())->format($timeDate->get_date_time_format());

        /** @var \User|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMock('User');
        $user->id = create_guid();

        $event = new ReminderEvent('update' . rand(1000, 1999));

        $event->setBean($call);
        $result = $this->builder->build($event, '', $user, $messageSignature);
        $this->assertEquals($expectedKeys, array_keys($result));
    }

    /**
     * Test that MessageBuilder has Base level.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\MessageBuilder::getLevel
     */
    public function testGetLevelReturnsBase()
    {
        $this->assertEquals(
            ReminderMessageBuilder::LEVEL_MODULE,
            $this->builder->getLevel()
        );
    }

    /**
     * Data provider for testSupports.
     *
     * @see MessageBuilderTest::testSupports
     * @return array
     */
    public static function supportsProvider()
    {
        return array(
            'beanEvent' => array(
                'event' => 'Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event',
                'eventName' => 'update' . rand(1000, 1999),
                'expectedResult' => true,
            ),
            'notSupportedEvent' => array(
                'eventClass' => 'Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event',
                'eventName' => 'update' . rand(2000, 2999),
                'expectedResult' => false,
            ),
        );
    }

    /**
     * Test that BeanEmitter Event is supported, any other Event isn't.
     *
     * @dataProvider supportsProvider
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\MessageBuilder::supports
     * @param string $eventClass
     * @param string $eventName
     * @param array $expectedResult
     */
    public function testSupports($eventClass, $eventName, $expectedResult)
    {
        $event = new $eventClass($eventName);
        $this->assertEquals($expectedResult, $this->builder->supports($event));
    }
}
