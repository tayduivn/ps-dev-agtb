<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

require_once 'clients/base/api/TreeApi.php';

/**
 * Test for TreeApi
 */
class TreeApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var TreeApi
     */
    protected $treeApi;

    /**
     * @var RestService
     */
    protected $serviceMock;

    /**
     * All created bean ids.
     *
     * @var array
     */
    public static $beanIds = array();

    /**
     * All created beans in tree view.
     *
     * @var array
     */
    public static $tree = array();

    /**
     * Root bean with children
     *
     * @var SugarBean
     */
    public static $beanRootWithChildren;

    /**
     * Root bean without children
     *
     * @var SugarBean
     */
    public static $beanRootWithoutChildren;

    /**
     * Child bean with children
     *
     * @var SugarBean
     */
    public static $beanChildWithChildren;

    /**
     * Child bean without children
     *
     * @var SugarBean
     */
    public static $beanChildWithoutChildren;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        // load test tree
        for ($i = 5; $i > 0; $i--) {
            $beans = array();
            $countBeans = rand(3, 5);

            for ($j = $countBeans; $j > 0; $j--) {
                $tb = BeanFactory::newBean('KBSTopics');
                $tb->name = 'SugarKBSTopic' . mt_rand();
                $tb->save();
                $GLOBALS['db']->commit();
                $beans[] = $tb;
                self::$beanIds[] = $tb->id;
            }

            if (isset($rootBean)) {
                $rootBean->load_relationship('subnodes');
                foreach ($beans as $child) {
                    $rootBean->subnodes->add($child);
                }
            }

            $rootIndex = rand(0, $countBeans - 1);
            $rootBean = $beans[$rootIndex];

            if (empty(self::$beanRootWithChildren)) {
                self::$beanRootWithChildren = $rootBean;
            } else if (empty(self::$beanChildWithChildren)) {
                self::$beanChildWithChildren = $rootBean;
            }

            do {
                $index = rand(0, $countBeans - 1);
            } while ($index == $rootIndex);

            if (empty(self::$beanRootWithoutChildren)) {
                self::$beanRootWithoutChildren = $beans[$index];
            } else if (empty(self::$beanChildWithoutChildren)) {
                self::$beanChildWithoutChildren = $beans[$index];
            }

            if (empty(self::$tree)) {
                self::$tree = $beans;
            }
        }
        $GLOBALS['db']->commit();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        $GLOBALS['db']->query('DELETE FROM kbstopics WHERE id IN (\'' . implode("', '", self::$beanIds) . '\')');

        self::$beanIds = array();
        self::$tree = array();

        self::$beanRootWithChildren = null;
        self::$beanRootWithoutChildren = null;
        self::$beanChildWithChildren = null;
        self::$beanChildWithoutChildren = null;

    }

    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->treeApi = new TreeApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Test get filtered subtree using root bean where children beans exists.
     */
    public function testFilterSubTreeUsingRootWithChildren()
    {
        $result = $this->treeApi->filterSubTree($this->serviceMock, array(
            'module' => 'KBSTopics',
            'link_name' => 'subnodes',
            'record' => self::$beanRootWithChildren->id
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('records', $result);
        $this->assertNotCount(0, $result['records']);

        $recordWithSubnodes = null;
        // checking records.
        foreach ($result['records'] as $record) {
            $this->assertInternalType('array', $record);
            $this->assertArrayHasKey('id', $record);
            $this->assertArrayHasKey('parent_id', $record);
            $this->assertArrayHasKey('_module', $record);
            $this->assertArrayHasKey('subnodes', $record);
            $this->assertEquals('KBSTopics', $record['_module']);
            $this->assertContains($record['id'], self::$beanIds);
            $this->assertEquals(self::$beanRootWithChildren->id, $record['parent_id']);
            $this->assertArrayHasKey('subnodes', $record);
            $this->assertInternalType('array', $record['subnodes']);
            $this->assertArrayHasKey('records', $record['subnodes']);

            if (count($record['subnodes']['records']) > 0) {
                $recordWithSubnodes = $record;
            }
        }
        // checking subnodes
        $this->assertNotEmpty($recordWithSubnodes);

        foreach ($recordWithSubnodes['subnodes']['records'] as $record) {
            $this->assertInternalType('array', $record);
            $this->assertArrayHasKey('id', $record);
            $this->assertArrayHasKey('parent_id', $record);
            $this->assertArrayHasKey('_module', $record);
            $this->assertEquals('KBSTopics', $record['_module']);
            $this->assertContains($record['id'], self::$beanIds);
            $this->assertEquals($recordWithSubnodes['id'], $record['parent_id']);
            $this->assertArrayHasKey('subnodes', $record);
            $this->assertInternalType('array', $record['subnodes']);
            $this->assertArrayHasKey('records', $record['subnodes']);
        }
    }

    /**
     * Test get filtered subtree using root bean where children beans not exists.
     */
    public function testFilterSubTreeUsingRootWithoutChildren()
    {
        $result = $this->treeApi->filterSubTree($this->serviceMock, array(
            'module' => 'KBSTopics',
            'link_name' => 'subnodes',
            'record' => self::$beanRootWithoutChildren->id
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('records', $result);
        $this->assertCount(0, $result['records']);
    }

    /**
     * Test get filtered subtree using child bean where children beans exists.
     */
    public function testFilterSubTreeUsingChildWithChildren()
    {
        $result = $this->treeApi->filterSubTree($this->serviceMock, array(
            'module' => 'KBSTopics',
            'link_name' => 'subnodes',
            'record' => self::$beanChildWithChildren->id,
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('records', $result);
        $this->assertNotCount(0, $result['records']);

        $recordWithSubnodes = null;
        // checking records.
        foreach ($result['records'] as $record) {
            $this->assertInternalType('array', $record);
            $this->assertArrayHasKey('id', $record);
            $this->assertArrayHasKey('parent_id', $record);
            $this->assertArrayHasKey('_module', $record);
            $this->assertArrayHasKey('subnodes', $record);
            $this->assertEquals('KBSTopics', $record['_module']);
            $this->assertContains($record['id'], self::$beanIds);
            $this->assertEquals(self::$beanChildWithChildren->id, $record['parent_id']);
            $this->assertArrayHasKey('subnodes', $record);
            $this->assertInternalType('array', $record['subnodes']);
            $this->assertArrayHasKey('records', $record['subnodes']);

            if (count($record['subnodes']['records']) > 0) {
                $recordWithSubnodes = $record;
            }
        }
        // checking subnodes
        $this->assertNotEmpty($recordWithSubnodes);

        foreach ($recordWithSubnodes['subnodes']['records'] as $record) {
            $this->assertInternalType('array', $record);
            $this->assertArrayHasKey('id', $record);
            $this->assertArrayHasKey('parent_id', $record);
            $this->assertArrayHasKey('_module', $record);
            $this->assertEquals('KBSTopics', $record['_module']);
            $this->assertContains($record['id'], self::$beanIds);
            $this->assertEquals($recordWithSubnodes['id'], $record['parent_id']);
            $this->assertArrayHasKey('subnodes', $record);
            $this->assertInternalType('array', $record['subnodes']);
            $this->assertArrayHasKey('records', $record['subnodes']);
        }
    }

    /**
     * Test get filtered subtree using child bean where children beans not exists.
     */
    public function testFilterSubTreeUsingChildWithoutChildren()
    {
        $result = $this->treeApi->filterSubTree($this->serviceMock, array(
            'module' => 'KBSTopics',
            'link_name' => 'subnodes',
            'record' => self::$beanChildWithoutChildren->id
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('records', $result);
        $this->assertCount(0, $result['records']);
    }

    /**
     * Test get filtered subtree using limit.
     */
    public function testFilterSubTreeLimit()
    {
        $result = $this->treeApi->filterSubTree($this->serviceMock, array(
            'module' => 'KBSTopics',
            'link_name' => 'subnodes',
            'record' => self::$beanRootWithChildren->id,
            'max_num' => 1,
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('records', $result);
        $this->assertCount(1, $result['records']);
    }

    /**
     * Test get filtered subtree with depth tree.
     */
    public function testFilterSubTreeDepth()
    {
        $result = $this->treeApi->filterSubTree($this->serviceMock, array(
            'module' => 'KBSTopics',
            'link_name' => 'subnodes',
            'record' => self::$beanRootWithChildren->id,
            'depth' => 1,
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('records', $result);
        $this->assertNotCount(0, $result['records']);

        $recordWithSubnodes = null;

        foreach ($result['records'] as $record) {
            $this->assertInternalType('array', $record);
            $this->assertArrayHasKey('id', $record);
            $this->assertArrayHasKey('parent_id', $record);
            $this->assertArrayHasKey('_module', $record);
            $this->assertEquals('KBSTopics', $record['_module']);
            $this->assertArrayHasKey('subnodes', $record);
            $this->assertInternalType('array', $record['subnodes']);
            $this->assertArrayHasKey('records', $record['subnodes']);

            if (count($record['subnodes']['records']) > 0) {
                $recordWithSubnodes = $record;
            }
        }
        // checking subnodes
        $this->assertNotEmpty($recordWithSubnodes);

        foreach ($recordWithSubnodes['subnodes']['records'] as $record) {
            $this->assertInternalType('array', $record);
            $this->assertArrayHasKey('id', $record);
            $this->assertArrayHasKey('parent_id', $record);
            $this->assertArrayHasKey('_module', $record);
            $this->assertEquals('KBSTopics', $record['_module']);
            $this->assertContains($record['id'], self::$beanIds);
            $this->assertEquals($recordWithSubnodes['id'], $record['parent_id']);
            // depth=1, subnodes not exists.
            $this->assertArrayNotHasKey('subnodes', $record);
        }
    }

    /**
     * Test full tree.
     */
    public function testFilterTree()
    {
        $result = $this->treeApi->filterTree($this->serviceMock, array(
            'module' => 'KBSTopics',
            'link_name' => 'subnodes'
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('records', $result);
        $this->assertNotCount(0, $result['records']);

        $recordWithSubnodes = null;
        $recordIds = array();
        // checking records.
        foreach ($result['records'] as $record) {
            $this->assertInternalType('array', $record);
            $this->assertArrayHasKey('id', $record);
            $this->assertArrayHasKey('parent_id', $record);
            $this->assertArrayHasKey('_module', $record);
            $this->assertArrayHasKey('subnodes', $record);
            $this->assertEquals('KBSTopics', $record['_module']);
            $this->assertArrayHasKey('subnodes', $record);
            $this->assertInternalType('array', $record['subnodes']);
            $this->assertArrayHasKey('records', $record['subnodes']);

            if (count($record['subnodes']['records']) > 0) {
                $recordWithSubnodes = $record;
            }
            $recordIds[] = $record['id'];
        }

        // checking top tree nodes
        foreach (self::$tree as $bean) {
            $this->assertContains($bean->id, $recordIds);
        }

        // checking subnodes
        $this->assertNotEmpty($recordWithSubnodes);

        foreach ($recordWithSubnodes['subnodes']['records'] as $record) {
            $this->assertInternalType('array', $record);
            $this->assertArrayHasKey('id', $record);
            $this->assertArrayHasKey('parent_id', $record);
            $this->assertArrayHasKey('_module', $record);
            $this->assertEquals('KBSTopics', $record['_module']);
            $this->assertEquals($recordWithSubnodes['id'], $record['parent_id']);
            $this->assertArrayHasKey('subnodes', $record);
            $this->assertInternalType('array', $record['subnodes']);
            $this->assertArrayHasKey('records', $record['subnodes']);
        }
    }

    /**
     * Test full tree with limit.
     */
    public function testFilterTreeLimit()
    {
        $result = $this->treeApi->filterTree($this->serviceMock, array(
            'module' => 'KBSTopics',
            'link_name' => 'subnodes',
            'max_num' => 1,
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('records', $result);
        $this->assertCount(1, $result['records']);
    }

    /**
     * Test full tree with depth.
     */
    public function testFilterTreeDepth()
    {
        $result = $this->treeApi->filterTree($this->serviceMock, array(
            'module' => 'KBSTopics',
            'link_name' => 'subnodes',
            'depth' => 1,
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('records', $result);
        $this->assertNotCount(0, $result['records']);

        $recordWithSubnodes = null;

        foreach ($result['records'] as $record) {
            $this->assertInternalType('array', $record);
            $this->assertArrayHasKey('id', $record);
            $this->assertArrayHasKey('parent_id', $record);
            $this->assertArrayHasKey('_module', $record);
            $this->assertEquals('KBSTopics', $record['_module']);
            $this->assertArrayHasKey('subnodes', $record);
            $this->assertInternalType('array', $record['subnodes']);
            $this->assertArrayHasKey('records', $record['subnodes']);

            if (count($record['subnodes']['records']) > 0) {
                $recordWithSubnodes = $record;
            }
        }
        // checking subnodes
        $this->assertNotEmpty($recordWithSubnodes);

        foreach ($recordWithSubnodes['subnodes']['records'] as $record) {
            $this->assertInternalType('array', $record);
            $this->assertArrayHasKey('id', $record);
            $this->assertArrayHasKey('parent_id', $record);
            $this->assertArrayHasKey('_module', $record);
            $this->assertEquals('KBSTopics', $record['_module']);
            $this->assertEquals($recordWithSubnodes['id'], $record['parent_id']);
            // depth=1, subnodes not exists.
            $this->assertArrayNotHasKey('subnodes', $record);
        }
    }
}
