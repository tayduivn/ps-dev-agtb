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

namespace Sugarcrm\SugarcrmTests\JobQueue\Handler;

use Sugarcrm\Sugarcrm\JobQueue\Dispatcher\DispatcherInterface;
use Sugarcrm\Sugarcrm\JobQueue\Handler\HandlerRegistry;

class HandlerRegistryTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var HandlerRegistry
     */
    protected $registry;

    /**
     * @var DispatcherInterface
     */
    protected $dispatcherMock;

    public function setUp()
    {
        $this->registry = new HandlerRegistry();
        $this->dispatcherMock = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Dispatcher\DispatcherInterface',
            array('dispatch')
        );
    }

    /**
     * Register handler class that implement RunnableInterface.
     */
    public function testHandler()
    {
        $handlerMock = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Handler\RunnableInterface',
            array('run')
        );
        $expected = 'handlerStub';
        $this->registry->add($expected, get_class($handlerMock), $this->dispatcherMock);
        $actual = $this->registry->get($expected);
        $this->assertEquals($expected, $actual['name']);
    }
}
