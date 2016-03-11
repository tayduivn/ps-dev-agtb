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

namespace Sugarcrm\SugarcrmTests\Trigger\Repair;

use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * Testing correct implementation interface Iterator and filling buffer.
 *
 * @covers \Sugarcrm\Sugarcrm\Trigger\Repair\BeanIterator
 */
class BeanIteratorTest extends \Sugar_PHPUnit_Framework_TestCase
{
    const MODULE = 'Meetings';

    const CHUNK_SIZE = 10;

    /** @var array */
    protected $fetchedLists = array();

    /** @var \Meeting|\PHPUnit_Framework_MockObject_MockObject*/
    protected $meeting = null;

    /** @var string */
    protected $nowTime = null;

    /** @var \Sugarcrm\Sugarcrm\Trigger\Repair\BeanIterator|\PHPUnit_Framework_MockObject_MockObject */
    protected $beanIterator = null;

    /** @var \SugarQuery|\PHPUnit_Framework_MockObject_MockObject */
    protected $sugarQuery1 = null;

    /** @var \SugarQuery|\PHPUnit_Framework_MockObject_MockObject */
    protected $sugarQuery2 = null;

    /** @var \SugarQuery|\PHPUnit_Framework_MockObject_MockObject */
    protected $sugarQuery3 = null;

    /** @var \SugarQuery_Builder_Andwhere|\PHPUnit_Framework_MockObject_MockObject */
    protected $where1 = null;

    /** @var \SugarQuery_Builder_Andwhere|\PHPUnit_Framework_MockObject_MockObject */
    protected $where2 = null;

    /** @var \SugarQuery_Builder_Andwhere|\PHPUnit_Framework_MockObject_MockObject */
    protected $where3 = null;

    /** @var \TimeDate|\PHPUnit_Framework_MockObject_MockObject */
    protected $timeDate = null;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->nowTime = 'nowTime' . rand(1000, 9999);
        \BeanFactory::setBeanClass(static::MODULE, 'Sugarcrm\SugarcrmTests\Trigger\Repair\MeetingsCRYS1219');

        $this->beanIterator = $this->getMock(
            'Sugarcrm\Sugarcrm\Trigger\Repair\BeanIterator',
            array('getSugarQuery', 'getTimeDate'),
            array(static::MODULE, static::CHUNK_SIZE)
        );

        $this->meeting = $this->getMockBuilder('Meeting')
            ->disableOriginalConstructor()
            ->getMock();
        MeetingsCRYS1219::$mock = $this->meeting;

        $this->sugarQuery1 = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $this->sugarQuery2 = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $this->sugarQuery3 = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $this->beanIterator->method('getSugarQuery')
            ->will($this->onConsecutiveCalls($this->sugarQuery1, $this->sugarQuery2, $this->sugarQuery3));

        $this->timeDate = $this->getMock('TimeDate');
        $this->beanIterator->method('getTimeDate')->willReturn($this->timeDate);
        $this->timeDate->method('nowDb')->willReturn($this->nowTime);

        $this->where1 = $this->getMock('SugarQuery_Builder_Andwhere', array(), array($this->sugarQuery1));
        $this->sugarQuery1->method('where')
            ->willReturn($this->where1);
        $this->where2 = $this->getMock('SugarQuery_Builder_Andwhere', array(), array($this->sugarQuery2));
        $this->sugarQuery2->method('where')
            ->willReturn($this->where2);
        $this->where3 = $this->getMock('SugarQuery_Builder_Andwhere', array(), array($this->sugarQuery3));
        $this->sugarQuery3->method('where')
            ->willReturn($this->where3);

        $this->fetchedLists[0] = array(
            $this->getMockBuilder('Meeting')->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder('Meeting')->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder('Meeting')->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder('Meeting')->disableOriginalConstructor()->getMock(),
        );
        $this->fetchedLists[1] = array(
            $this->getMockBuilder('Meeting')->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder('Meeting')->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder('Meeting')->disableOriginalConstructor()->getMock(),
        );

        foreach ($this->fetchedLists as $iteration => $list) {
            foreach ($list as $key => $meeting) {
                $this->fetchedLists[$iteration][$key]->id = Uuid::uuid1();
                $this->fetchedLists[$iteration][$key]->foundUnserIds = array(Uuid::uuid1(), Uuid::uuid1());
                $this->fetchedLists[$iteration][$key]->users = $this->getMockBuilder('Link2')
                    ->disableOriginalConstructor()
                    ->getMock();
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        \BeanFactory::setBeanClass(static::MODULE);
        parent::tearDown();
    }

    /**
     * Testing correct implementation interface Iterator and is correctly fetched data from db.
     *
     * @covers \Sugarcrm\Sugarcrm\Trigger\Repair\BeanIterator::current
     * @covers \Sugarcrm\Sugarcrm\Trigger\Repair\BeanIterator::next
     * @covers \Sugarcrm\Sugarcrm\Trigger\Repair\BeanIterator::key
     * @covers \Sugarcrm\Sugarcrm\Trigger\Repair\BeanIterator::valid
     * @covers \Sugarcrm\Sugarcrm\Trigger\Repair\BeanIterator::rewind
     * @covers \Sugarcrm\Sugarcrm\Trigger\Repair\BeanIterator::fillBuffer
     */
    public function testListIterator()
    {
        foreach ($this->fetchedLists as $iteration => $list) {
            foreach ($list as $key => $meeting) {
                $meeting->expects($this->once())->method('load_relationship');
                $meeting->users
                    ->method('get')
                    ->willReturn($meeting->foundUnserIds);
            }
        }

        $this->meeting->expects($this->at(0))->method('fetchFromQuery')->willReturn($this->fetchedLists[0]);
        $this->meeting->expects($this->at(1))->method('fetchFromQuery')->willReturn($this->fetchedLists[1]);
        $this->meeting->expects($this->at(2))->method('fetchFromQuery')->willReturn(array());
        $this->sugarQuery1->expects($this->once())
            ->method('from')
            ->with($this->isInstanceOf('Meeting'), $this->equalTo(array('team_security' => false)));
        $this->sugarQuery1->expects($this->once())
            ->method('limit')
            ->with($this->equalTo(static::CHUNK_SIZE));
        $this->sugarQuery1->expects($this->once())
            ->method('offset')
            ->with($this->equalTo(0));
        $this->where1->expects($this->once())
            ->method('gt')
            ->with($this->equalTo('date_start'), $this->equalTo($this->nowTime), $this->isInstanceOf('Meeting'));

        $this->sugarQuery2->expects($this->once())
            ->method('from')
            ->with($this->isInstanceOf('Meeting'), $this->equalTo(array('team_security' => false)));
        $this->sugarQuery2->expects($this->once())
            ->method('limit')
            ->with($this->equalTo(static::CHUNK_SIZE));
        $this->sugarQuery2->expects($this->once())
            ->method('offset')
            ->with($this->equalTo(4));
        $this->where2->expects($this->once())
            ->method('gt')
            ->with($this->equalTo('date_start'), $this->equalTo($this->nowTime), $this->isInstanceOf('Meeting'));

        $this->sugarQuery3->expects($this->once())
            ->method('from')
            ->with($this->isInstanceOf('Meeting'), $this->equalTo(array('team_security' => false)));
        $this->sugarQuery3->expects($this->once())
            ->method('limit')
            ->with($this->equalTo(static::CHUNK_SIZE));
        $this->sugarQuery3->expects($this->once())
            ->method('offset')
            ->with($this->equalTo(7));
        $this->where3->expects($this->once())
            ->method('gt')
            ->with($this->equalTo('date_start'), $this->equalTo($this->nowTime), $this->isInstanceOf('Meeting'));

        $ids = array();
        foreach ($this->beanIterator as $bean) {
            $this->assertNotContains($bean->id, $ids, 'Duplicate beans');
            $ids[] = $bean->id;
            $this->assertContains($bean->foundUnserIds[0], $bean->users_arr);
            $this->assertContains($bean->foundUnserIds[1], $bean->users_arr);
            $this->assertCount(count($bean->foundUnserIds), $bean->users_arr);
        }
        $this->assertCount(count($this->fetchedLists[0]) + count($this->fetchedLists[1]), $ids);
    }
}

/**
 * Stub class for Meeting bean.
 */
class MeetingsCRYS1219 extends \Meeting
{
    /** @var \Meeting|\PHPUnit_Framework_MockObject_MockObject */
    public static $mock = null;

    public function fetchFromQuery($query)
    {
        return static::$mock->fetchFromQuery($query);
    }
}
