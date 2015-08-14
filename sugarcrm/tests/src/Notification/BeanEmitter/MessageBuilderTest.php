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
namespace Sugarcrm\SugarcrmTests\Notification\BeanEmitter;

use Sugarcrm\Sugarcrm\Notification\BeanEmitter\MessageBuilder;
use Sugarcrm\Sugarcrm\Notification\BeanEmitter\Event;

/**
 * @covers Sugarcrm\Sugarcrm\Notification\BeanEmitter\MessageBuilder
 */
class MessageBuilderTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var MessageBuilder
     */
    protected $builder;

    /**
     * @var Sugarcrm\Sugarcrm\Notification\BeanEmitter\Event
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
        $this->builder = new MessageBuilder();

        $this->bean = \BeanFactory::getBean('Accounts');
        $this->bean->name = 'TestAccount';
        $this->user = \SugarTestUserUtilities::createAnonymousUser();

        $this->event = new Event('event1');
        $this->event->setBean($this->bean);
    }

    public function tearDown()
    {
        \SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        parent::tearDown();
    }

    /**
     * Test build() with different message signatures.
     * @param array $messageSignature Message signature to test.
     * @param array $message Expected output message.
     * @covers Sugarcrm\Sugarcrm\Notification\BeanEmitter\MessageBuilder::build
     * @dataProvider messageSignatureProvider
     */
    public function testBuildWithDifferentMessageSignatures($messageSignature, $message)
    {
        $result = $this->builder->build($this->event, $this->user, $messageSignature);
        $this->assertEquals($message, $result);
    }

    /**
     * Test that MessageBuilder has Base level.
     * @covers Sugarcrm\Sugarcrm\Notification\BeanEmitter\MessageBuilder::getLevel
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
     * @covers Sugarcrm\Sugarcrm\Notification\BeanEmitter\MessageBuilder::supports
     */
    public function testSupports()
    {
        $this->assertTrue(
            $this->builder->supports(new Sugarcrm\Sugarcrm\Notification\BeanEmitter\Event('event1'))
        );
        $this->assertFalse(
            $this->builder->supports(new Sugarcrm\Sugarcrm\Notification\ApplicationEmitter\Event('event2'))
        );
    }

    /**
     * Data provider for testBuildWithDifferentMessageSignatures().
     * @return array
     */
    public function messageSignatureProvider()
    {
        return array(
            array(
                array(),
                array()
            ),
            array(
                array('title'),
                array('title' => "{$this->event} triggered")
            ),
            array(
                array('text'),
                array('text' => "Triggered in {$this->bean->module_name}:'{$this->bean->name}")
            ),
            array(
                array('title', 'text'),
                array(
                    'title' => "{$this->event} triggered",
                    'text' => "Triggered in {$this->bean->module_name}:'{$this->bean->name}",
                )
            ),
        );
    }
}
