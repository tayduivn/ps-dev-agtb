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

namespace Sugarcrm\SugarcrmTests\Notification\Emitter\Bean;

use Sugarcrm\Sugarcrm\Notification\Emitter\Bean\MessageBuilder as BeanMessageBuilder;
use Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event as BeanEvent;
use Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderInterface;
use \Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event as ApplicationEvent;

/**
 * Class MessageBuilderTest
 *
 * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\MessageBuilder
 */
class MessageBuilderTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var BeanMessageBuilder */
    protected $builder;

    /** @var BeanEvent */
    protected $event;

    /** @var \SugarBean */
    protected $bean;

    /** @var \User */
    protected $user;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->user = new \User();
        $this->user->id = create_guid();

        $this->builder = new BeanMessageBuilder();

        $this->bean = new \Account();
        $this->bean->name = 'Name' . rand(1000, 1999);

        $this->event = new BeanEvent('event' . rand(1000, 1999));
        $this->event->setBean($this->bean);
    }

    /**
     * Data provider for testBuildWithDifferentMessageSignatures.
     *
     * @see MessageBuilderTest::testBuildWithDifferentMessageSignatures
     * @return array
     */
    public static function messageSignatureProvider()
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
     * Test build with different message signatures.
     *
     * @dataProvider messageSignatureProvider
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\MessageBuilder::build
     * @param array $messageSignature Message signature to test.
     * @param array $expectedKeys Expected array messages keys.
     */
    public function testBuildWithDifferentMessageSignatures($messageSignature, $expectedKeys)
    {
        $result = $this->builder->build($this->event, '', $this->user, $messageSignature);
        $this->assertEquals($expectedKeys, array_keys($result));
    }

    /**
     * Test that any event except BeanEvent does not support.
     *
     * @covers \Sugarcrm\Sugarcrm\Notification\Emitter\Bean\MessageBuilder::supports
     */
    public function testSupportsWrongEvent()
    {
        $event = new ApplicationEvent('update');
        $this->assertFalse($this->builder->supports($event));
    }

    /**
     * Test that BeanEvent supports by builder.
     *
     * @covers \Sugarcrm\Sugarcrm\Notification\Emitter\Bean\MessageBuilder::supports
     */
    public function testSupportsCorrectEvent()
    {
        $this->assertTrue($this->builder->supports($this->event));
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
}
