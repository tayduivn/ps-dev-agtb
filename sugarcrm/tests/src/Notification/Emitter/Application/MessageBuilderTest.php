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

namespace Sugarcrm\SugarcrmTests\Notification\Emitter\Application;

use Sugarcrm\Sugarcrm\Notification\Emitter\Application\MessageBuilder as ApplicationMessageBuilder;
use Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderInterface;

/**
 * Class MessageBuilderTest
 *
 * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Application\MessageBuilder
 */
class MessageBuilderTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var ApplicationMessageBuilder */
    protected $messageBuilder = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->messageBuilder = new ApplicationMessageBuilder();
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Application\MessageBuilder::build
     */
    public function testBuild()
    {
        $this->markTestIncomplete('Waiting for requirements');
    }

    /**
     * Application message builder should have base level.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Application\MessageBuilder::getLevel
     */
    public function testGetLevel()
    {
        $this->assertEquals(MessageBuilderInterface::LEVEL_BASE, $this->messageBuilder->getLevel());
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Application\MessageBuilder::supports
     */
    public function testSupports()
    {
        $this->markTestIncomplete('Waiting for requirements');
    }
}
