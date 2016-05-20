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


namespace Sugarcrm\SugarcrmTests\Dav\Cal\Rebuild;

use Sugarcrm\Sugarcrm\Dav\Cal\Rebuild\BeanIterator;
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * Class BeanIteratorTest
 *
 * @covers Sugarcrm\Sugarcrm\Dav\Cal\Rebuild\BeanIterator
 */
class BeanIteratorTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var BeanIterator
     */
    protected $beanIterator = null;

    /**
     * @var string
     */
    protected $module = '';

    /**
     * @var \SugarTestDatabaseMock
     */
    protected $db = null;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        \SugarTestHelper::setUp('moduleList');
        \SugarTestHelper::setUp('beanList');

        $this->db = \SugarTestHelper::setUp('mock_db');

        $this->module = 'Meeting:module:' . rand(1000, 9999);

        \BeanFactory::setBeanClass($this->module, __NAMESPACE__ . '\MeetingCRYS1301');

        $this->beanIterator = new BeanIterator($this->module);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        \BeanFactory::setBeanClass($this->module, null);
        \SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Testing correct implementation interface Iterator and is correctly fetched data from db.
     *
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Rebuild\BeanIterator::current
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Rebuild\BeanIterator::next
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Rebuild\BeanIterator::key
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Rebuild\BeanIterator::valid
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Rebuild\BeanIterator::rewind
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Rebuild\BeanIterator::fillBuffer
     */
    public function testBeanIterator()
    {
        $foundIdsList = array();
        $ids = array();
        $expectedIds = array(
            Uuid::uuid1(),
            Uuid::uuid1(),
            Uuid::uuid1(),
            Uuid::uuid1(),
        );

        foreach ($expectedIds as $id) {
            $foundIdsList[] = array('id' => $id);
        }
        $this->db->addQuerySpy('fill_ids', "/^SELECT meetings.id id FROM meetings/", $foundIdsList);

        foreach ($this->beanIterator as $bean) {
            $this->assertInstanceOf(__NAMESPACE__ . '\MeetingCRYS1301', $bean);
            $ids[] = $bean->id;
        }

        sort($ids);
        sort($expectedIds);
        $this->assertCount(count($expectedIds), $ids);
        $this->assertArraySubset($expectedIds, $ids);
    }
}

/**
 * Class MeetingCRYS1301
 */
class MeetingCRYS1301 extends \Meeting
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
