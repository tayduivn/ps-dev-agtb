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

namespace Sugarcrm\SugarcrmTests\Dav\Cal\JobQueue;

use Sugarcrm\Sugarcrm\Dav\Cal\JobQueue\Handler;

/**
 * Testing is correct current user on importing/exporting.
 *
 * @covers Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler
 */
class HandlerCRYS1492Test extends \Sugar_PHPUnit_Framework_TestCase
{

    /** @var \CalDavQueue|\PHPUnit_Framework_MockObject_MockObject */
    protected $queueBean = null;

    /** @var \CalDavQueue|\PHPUnit_Framework_MockObject_MockObject */
    protected $queueItem1 = null;

    /** @var \CalDavQueue|\PHPUnit_Framework_MockObject_MockObject */
    protected $queueItem2 = null;

    /** @var Handler */
    protected $handler = null;

    /** @var \CalDavSynchronization|\PHPUnit_Framework_MockObject_MockObject */
    protected $synchronization = null;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        \SugarTestHelper::setUp('current_user', array(0 => false));

        $this->queueBean = $this->getMockBuilder('CalDavQueue')
            ->disableOriginalConstructor()
            ->getMock();

        $this->queueItem1 = $this->getMockBuilder('CalDavQueue')
            ->disableOriginalConstructor()
            ->getMock();

        $this->queueItem2 = $this->getMockBuilder('CalDavQueue')
            ->disableOriginalConstructor()
            ->getMock();

        $this->synchronization = $this->getMockBuilder('CalDavSynchronization')
            ->disableOriginalConstructor()
            ->getMock();

        CalDavEventCollectionCRYS1492::$synchronizationObject = $this->synchronization;

        \BeanFactory::setBeanClass(
            'CalDavEvents',
            'Sugarcrm\SugarcrmTests\Dav\Cal\JobQueue\CalDavEventCollectionCRYS1492'
        );

        \BeanFactory::setBeanClass(
            'Users',
            'Sugarcrm\SugarcrmTests\Dav\Cal\JobQueue\UserCRYS1492'
        );

        CalDavEventCollectionCRYS1492::$queueObject = $this->queueBean;

        $eventId = create_guid();
        $this->handler = new Handler($eventId);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        \BeanFactory::setBeanClass('CalDavEvents');
        \BeanFactory::setBeanClass('Users');

        \SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * On importing/exporting current user should be same that's create queueItem.
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\JobQueue\Handler::run
     */
    public function testCurrentUser()
    {
        /** @var \User $queueItem1CurrentUsers */
        $queueItem1CurrentUsers = null;

        /** @var \User $queueItem2CurrentUsers */
        $queueItem2CurrentUsers = null;

        /** @var \User $originCurrentUser */
        $originCurrentUser = $GLOBALS['current_user'];

        $this->queueItem1->created_by = create_guid();
        $this->queueItem2->created_by = create_guid();

        $this->queueItem1
            ->method('save')
            ->with($this->callback(function () use (&$queueItem1CurrentUsers) {
                $queueItem1CurrentUsers = $GLOBALS['current_user'];
                return true;
            }));
        $this->queueItem2
            ->method('save')
            ->with($this->callback(function () use (&$queueItem2CurrentUsers) {
                $queueItem2CurrentUsers = $GLOBALS['current_user'];
                return true;
            }));

        $this->queueBean
            ->expects($this->at(0))
            ->method('findFirstQueued')
            ->willReturn($this->queueItem1);
        $this->queueBean
            ->expects($this->at(1))
            ->method('findFirstQueued')
            ->willReturn($this->queueItem2);
        $this->queueBean
            ->expects($this->at(2))
            ->method('findFirstQueued')
            ->willReturn(null);

        $this->handler->run();

        $this->assertEquals($queueItem1CurrentUsers->id, $this->queueItem1->created_by);
        $this->assertEquals($queueItem2CurrentUsers->id, $this->queueItem2->created_by);
        $this->assertEquals($originCurrentUser, $GLOBALS['current_user']);
    }
}

/**
 * Mock for \CalDavEventCollection.
 */
class CalDavEventCollectionCRYS1492 extends \CalDavEventCollection
{

    /** @var \CalDavQueue|\PHPUnit_Framework_MockObject_MockObject */
    public static $queueObject;

    /** @var \CalDavSynchronization|\PHPUnit_Framework_MockObject_MockObject */
    public static $synchronizationObject;

    public function getQueueObject()
    {
        return static::$queueObject;
    }

    public function getSynchronizationObject()
    {
        return static::$synchronizationObject;
    }

    /**
     * @inheritDoc
     */
    public function retrieve($id = '-1', $encode = true, $deleted = true)
    {
        $this->id = $id;

        return $this;
    }
}

/**
 * Mock for \User.
 */
class UserCRYS1492 extends \User
{

    /**
     * @inheritDoc
     */
    public function retrieve($id = '-1', $encode = true, $deleted = true)
    {
        $this->id = $id;

        return $this;
    }
}
