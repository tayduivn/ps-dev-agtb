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

namespace Sugarcrm\SugarcrmTests\Notification\Emitter\Application;

use Sugarcrm\Sugarcrm\Notification\Emitter\Application\Emitter as ApplicationEmitter;

/**
 * Class EmitterTest
 *
 * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Application\Emitter
 */
class EmitterTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var ApplicationEmitter */
    protected $applicationEmitter = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->applicationEmitter = new ApplicationEmitter();
    }

    /**
     * Checks whether the getEventPrototypeByString returns Application Event object.
     * String representation of the event should be its name.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Application\Emitter::getEventPrototypeByString
     * @dataProvider getEventPrototypeByStringReturnsCurrentEventObjectProvider
     * @param string $eventName
     */
    public function testGetEventPrototypeByStringReturnsCurrentEventObject($eventName)
    {
        $eventPrototype = $this->applicationEmitter->getEventPrototypeByString($eventName);
        $this->assertInstanceOf(
            'Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event',
            $eventPrototype
        );
        $this->assertEquals($eventName, (string)$eventPrototype);
    }

    /**
     * Data provider for testGetEventPrototypeByStringReturnsCurrentEventObject.
     *
     * @see EmitterTest::testGetEventPrototypeByStringReturnsCurrentEventObject
     * @return array
     */
    public static function getEventPrototypeByStringReturnsCurrentEventObjectProvider()
    {
        return array(
            'emptyString' => array(
                'eventName' => '',
            ),
            'notEmptyString' => array(
                'eventName' => 'testString',
            )
        );
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Application\Emitter::getEventStrings
     */
    public function testGetEventStrings()
    {
        $this->markTestIncomplete('Waiting for list of application level events');
    }

    /**
     * Check if string representation of emitter is ApplicationEmitter.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Application\Emitter::__toString
     */
    public function testToString()
    {
        $this->assertEquals('ApplicationEmitter', (string)$this->applicationEmitter);
    }
}
