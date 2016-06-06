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

namespace Sugarcrm\SugarcrmTests\Dav\Cal\Adapter\MeetingsAdapter;

use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\MeetingsAdapter\Factory;

/**
 * Class FactoryTest
 * @package Sugarcrm\SugarcrmTests\Dav\Cal\Adapter\MeetingsAdapter
 * @covers Sugarcrm\Sugarcrm\Dav\Cal\Adapter\MeetingsAdapter\Factory
 */
class FactoryTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var Factory|\PHPUnit_Framework_MockObject_MockObject */
    protected $factoryMock;

    /** @var \LoggerManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $loggerMock;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->loggerMock = $this->getMockBuilder('\LoggerManager')->disableOriginalConstructor()->getMock();
        $this->factoryMock = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\MeetingsAdapter\Factory', array(
            'getLogger'
        ));
        $this->factoryMock->method('getLogger')->willReturn($this->loggerMock);
    }

    /**
     * Checks getting adapter for module Meetings.
     * @covers Factory::getAdapter
     */
    public function testGetAdapter()
    {
        $this->assertInstanceOf(
            'Sugarcrm\Sugarcrm\Dav\Cal\Adapter\MeetingsAdapter\DataAdapter',
            $this->factoryMock->getAdapter()
        );
    }

    /**
     * Checks getting properties adapter for module Meetings.
     * @covers Factory::getPropertiesAdapter
     */
    public function testGetPropertiesAdapter()
    {
        $this->assertInstanceOf(
            'Sugarcrm\Sugarcrm\Dav\Cal\Adapter\MeetingsAdapter\CustomPropertiesAdapter',
            $this->factoryMock->getPropertiesAdapter()
        );
    }
}
