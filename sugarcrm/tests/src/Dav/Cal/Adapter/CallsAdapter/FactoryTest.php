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

namespace Sugarcrm\SugarcrmTests\Dav\Cal\Adapter\CallsAdapter;

use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CallsAdapter\Factory;

/**
 * Class FactoryTest
 * @package Sugarcrm\SugarcrmTests\Dav\Cal\Adapter\CallsAdapter
 * @covers Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CallsAdapter\Factory
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
        $this->factoryMock = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CallsAdapter\Factory', array(
            'getLogger'
        ));
        $this->factoryMock->method('getLogger')->willReturn($this->loggerMock);
    }

    /**
     * Checks getting adapter for module Calls.
     * @covers Factory::getAdapter
     */
    public function testGetAdapter()
    {
        $this->assertInstanceOf(
            'Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CallsAdapter\DataAdapter',
            $this->factoryMock->getAdapter()
        );
    }

    /**
     * Checks getting properties adapter for module Calls.
     * @covers Factory::getPropertiesAdapter
     */
    public function testGetPropertiesAdapter()
    {
        $this->assertInstanceOf(
            'Sugarcrm\Sugarcrm\Dav\Cal\Adapter\CallsAdapter\CustomPropertiesAdapter',
            $this->factoryMock->getPropertiesAdapter()
        );
    }
}
