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

    /**
     * Root node 
     *
     * @var CategoryMock $root 
     */
    public static $root;

    /**
     * Nested set test data 
     * @var array
     */
    public static $testData = array(
        array('lft' => '2', 'rgt' => '9', 'level' => '1'),
        array('lft' => '3', 'rgt' => '4', 'level' => '2'),
        array('lft' => '5', 'rgt' => '6', 'level' => '2'),
        array('lft' => '7', 'rgt' => '8', 'level' => '2'),
        array('lft' => '10', 'rgt' => '19', 'level' => '1'),
        array('lft' => '11', 'rgt' => '14', 'level' => '2'),
        array('lft' => '12', 'rgt' => '13', 'level' => '3'),
        array('lft' => '15', 'rgt' => '16', 'level' => '2'),
        array('lft' => '17', 'rgt' => '18', 'level' => '2'),
    );

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
        $GLOBALS['db']->query('DELETE FROM categories WHERE id IN (\'' . implode("', '", self::$beanIds) . '\')');

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
        $root = new Category();
        $root->name = 'SugarCategoryRoot' . mt_rand();
        self::$beanIds[] = $root->makeRoot();
        $root->rgt = (count(self::$testData) + $root->lft) * 2;
        $root->save();
        self::$root = $root;

        foreach (self::$testData as $node) {
            $bean = BeanFactory::newBean('Categories');
            $bean->name = 'SugarCategory' . mt_rand();
            $bean->lft = $node['lft'];
            $bean->rgt = $node['rgt'];
            $bean->level = $node['level'];
            $bean->root = $root->id;
            $bean->save();
            $GLOBALS['db']->commit();
            self::$beanIds[] = $bean->id;
        }
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Test tree for selected root API method.
     */
    public function testTree()
    {
        $result = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertTrue(array_key_exists('children', current($result)));
        $this->assertNotEmpty($result[0]['children']);
        $this->assertInternalType('array', $result[0]['children']);
    }

    /**
     * Test prepend node to target API method.
     */
    public function testPrepend()
    {
        $result = $this->treeApi->prepend($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'target' => self::$root->id,
            'name' => 'SugarCategory' . mt_rand(),
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('string', $result);

        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));


        $firstNode = array_shift($tree);
        $this->assertEquals($firstNode['id'], $result);
    }

    /**
     * Test append node to target API method.
     */
    public function testAppend()
    {
        $result = $this->treeApi->append($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'target' => self::$root->id,
            'name' => 'SugarCategory' . mt_rand(),
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('string', $result);

        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $lastNode = array_pop($tree);
        $this->assertEquals($lastNode['id'], $result);
    }

    /**
     * Test insert node before target API method.
     */
    public function testInsertBefore()
    {
        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $result = $this->treeApi->insertBefore($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'target' => $tree[1]['id'],
            'name' => 'SugarCategory' . mt_rand(),
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('string', $result);

        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $this->assertEquals($tree[1]['id'], $result);
    }

    /**
     * Test insert node before root should catch exception.
     */
    public function testInsertBeforeRoot()
    {
        $this->setExpectedException('Exception');

        $result = $this->treeApi->insertBefore($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'target' => self::$root->id,
            'name' => 'SugarCategory' . mt_rand(),
        ));
    }

    /**
     * Test insert node after target API method.
     */
    public function testInsertAfter()
    {
        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $result = $this->treeApi->insertAfter($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'target' => $tree[1]['id'],
            'name' => 'SugarCategory' . mt_rand(),
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('string', $result);

        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $this->assertEquals($tree[2]['id'], $result);
    }

    /**
     * Test insert node after root should catch exception.
     */
    public function testInsertAfterRoot()
    {
        $this->setExpectedException('Exception');

        $result = $this->treeApi->insertAfter($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'target' => self::$root->id,
            'name' => 'SugarCategory' . mt_rand(),
        ));
    }

    /**
     * Test move node before target API method.
     */
    public function testMoveBefore()
    {
        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $expect = array_reverse(array_slice($tree, 2));

        $result = $this->treeApi->moveBefore($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'record' => $tree[1]['id'],
            'target' => $tree[0]['id'],
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertTrue(array_key_exists('id', $result));
        $this->assertEquals($tree[1]['id'], $result['id']);

        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $this->assertEquals($expect, array_slice($tree, 2));
    }

    /**
     * Test move node after target API method.
     */
    public function testMoveAfter()
    {
        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $expect = array_reverse(array_slice($tree, 2));

        $result = $this->treeApi->moveBefore($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'record' => $tree[0]['id'],
            'target' => $tree[1]['id'],
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertTrue(array_key_exists('id', $result));
        $this->assertEquals($tree[0]['id'], $result['id']);

        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $this->assertEquals($expect, array_slice($tree, 2));
    }

    /**
     * Test get node children API method.
     */
    public function testChildren()
    {
        $result = $this->treeApi->children($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'record' => self::$root->id,
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertEquals(2, count($result));

        foreach ($result as $item) {
            $itemBean = new Category;
            $itemBean->populateFromRow($item);
            $this->assertTrue($itemBean->isDescendantOf(self::$root));
        }
    }

    /**
     * Test get tree root nodes API method.
     */
    public function testRoots()
    {
        $result = $this->treeApi->roots($this->serviceMock, array(
            'module' => self::$root->module_dir,
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);

        foreach ($result as $item) {
            $this->assertEquals('1', $item['lft']);
        }
    }

    /**
     * Test get node parent API method.
     */
    public function testParent()
    {
        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $result = $this->treeApi->getParent($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'record' => $tree[0]['id'],
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertEquals(self::$root->id, $result['id']);
    }

    /**
     * Test get node previous sibling API method.
     */
    public function testPrev()
    {
        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $result = $this->treeApi->prev($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'record' => $tree[1]['id'],
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertEquals(self::$root->id, $result['root']);
        $this->assertEquals($tree[0]['id'], $result['id']);
    }

    /**
     * Test get node next sibling API method.
     */
    public function testNext()
    {
        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $result = $this->treeApi->next($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'record' => $tree[0]['id'],
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertEquals(self::$root->id, $result['root']);
        $this->assertEquals($tree[1]['id'], $result['id']);
    }

    /**
     * Test get node path API method.
     */
    public function testPath()
    {
        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $testNode = array_shift($tree[0]['children']);

        $result = $this->treeApi->path($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'record' => $testNode['id'],
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertEquals(2, count($result));

        list($root, $parent) = $result;

        $this->assertEquals(self::$root->id, $root['id']);
        $this->assertEquals($tree[0]['id'], $parent['id']);
    }

    /**
     * Test move node and set as first node API method.
     */
    public function testMoveFirst()
    {
        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $expected = array($tree[1]['id'], $tree[0]['id']);

        $result = $this->treeApi->moveFirst($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'record' => $tree[1]['id'],
            'target' => self::$root->id,
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertTrue(array_key_exists('id', $result));
        $this->assertEquals($tree[1]['id'], $result['id']);

        $updatedTree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $this->assertEquals($expected, array($updatedTree[0]['id'], $updatedTree[1]['id']));
    }

    /**
     * Test move node and set as last node API method.
     */
    public function testMoveLast()
    {
        $tree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $expected = array($tree[1]['id'], $tree[0]['id']);

        $result = $this->treeApi->moveLast($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'record' => $tree[0]['id'],
            'target' => self::$root->id,
        ));

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        $this->assertTrue(array_key_exists('id', $result));
        $this->assertEquals($tree[0]['id'], $result['id']);

        $updatedTree = $this->treeApi->tree($this->serviceMock, array(
            'module' => self::$root->module_dir,
            'root' => self::$root->id,
        ));

        $this->assertEquals($expected, array($updatedTree[0]['id'], $updatedTree[1]['id']));
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
