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

namespace Sugarcrm\SugarcrmTests\Notification\Emitter\Bean;

use Sugarcrm\Sugarcrm\Notification\Emitter\Bean\MessageBuilder;
use Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event;
use Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderInterface;

/**
 * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\MessageBuilder
 */
class MessageBuilderTest extends \Sugar_PHPUnit_Framework_TestCase
{
    const accountName = 'TestAccount';
    const eventName = 'event1';

    /**
     * @var MessageBuilder
     */
    protected $builder;

    /**
     * @var \Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event
     */
    protected $event;

    /**
     * @var \SugarBean
     */
    protected $bean;

    /**
     * @var \User
     */
    protected $user;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->user = \SugarTestHelper::setUp('current_user');

        $this->builder = new MessageBuilder();

        $this->bean = new \Account();
        $this->bean->name = static::accountName;

        $this->event = new Event(static::eventName);
        $this->event->setBean($this->bean);
    }

    public function tearDown()
    {
        \SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Test build() with different message signatures.
     *
     * @param array $messageSignature Message signature to test.
     * @param array $message Expected output message.
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\MessageBuilder::build
     * @dataProvider messageSignatureProvider
     */
    public function testBuildWithDifferentMessageSignatures($messageSignature, $message)
    {
        $result = $this->builder->build($this->event, '', $this->user, $messageSignature);
        $this->assertEquals($message, $result);
    }

    /**
     * Test that MessageBuilder has Base level.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\MessageBuilder::getLevel
     */
    public function testGetLevelReturnsBase()
    {
        $this->assertEquals(
            MessageBuilderInterface::LEVEL_BASE,
            $this->builder->getLevel()
        );
    }

    /**
     * Test that BeanEmitter Event is supported, any other Event isn't.
     *
     * @covers \Sugarcrm\Sugarcrm\Notification\Emitter\Bean\MessageBuilder::supports
     */
    public function testSupports()
    {
        $this->assertTrue(
            $this->builder->supports(new Event('event1'))
        );
        $this->assertFalse(
            $this->builder->supports(new \Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event('event2'))
        );
    }

    /**
     * Data provider for testBuildWithDifferentMessageSignatures().
     *
     * @return array
     */
    public static function messageSignatureProvider()
    {
        return array(
            array(
                array(),
                array(),
            ),
            array(
                array(
                    'title' => '',
                ),
                array(
                    'title' => static::eventName . " triggered",
                ),
            ),
            array(
                array(
                    'text' => '',
                ),
                array(
                    'text' => "Triggered in Accounts:'" . static::accountName . "'",
                ),
            ),
            array(
                array(
                    'title' => '',
                    'text' => '',
                ),
                array(
                    'title' => static::eventName . " triggered",
                    'text' => "Triggered in Accounts:'" . static::accountName . "'",
                ),
            ),
        );
    }
}
