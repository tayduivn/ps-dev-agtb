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
namespace Sugarcrm\SugarcrmTestUnit\modules\ProductTemplates\clients\base\api;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \ProductTemplateTreeApi
 */
class ProductTemplateTreeApiTest extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        \SugarAutoLoader::load('../../modules/ProductTemplates/clients/base/api/ProductTemplateApi.php');
        \SugarAutoLoader::load('../../include/SugarObjects/SugarConfig.php');

        parent::setup();
    }

    /**
     * @covers ::registerApiRest
     */
    public function testRegisterApiRest()
    {
        $mock = $this->getMockBuilder('\ProductTemplateTreeApi')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $compare = array(
            'tree' => array(
                'reqType' => 'GET',
                'path' => array('ProductTemplates', 'tree',),
                'pathVars' => array('module', 'type',),
                'method' => 'getTemplateTree',
                'shortHelp' => 'Returns a filterable tree structure of all Product Templates and Product Categories',
                'longHelp' => 'modules/ProductTemplates/clients/base/api/help/tree.html',
            ),
            'filterTree' => array(
                'reqType' => 'POST',
                'path' => array('ProductTemplates', 'tree',),
                'pathVars' => array('module', 'type',),
                'method' => 'getTemplateTree',
                'shortHelp' => 'Returns a filterable tree structure of all Product Templates and Product Categories',
                'longHelp' => 'modules/ProductTemplates/clients/base/api/help/tree.html',
            ),
        );

        $returnVal = $mock->registerApiRest();
        $this->assertEquals($compare, $returnVal);
    }

    /**
     * @covers ::generateNewLeaf
     * @dataProvider generateNewLeafProvider
     */
    public function testGenerateNewLeaf($type, $state)
    {
        $id = 'foo';
        $name = 'bar';
        $total = 10;
        $index = 0;

        $returnObj =  new \stdClass();
        $returnObj->id = $id;
        $returnObj->type = $type;
        $returnObj->data = $name;
        $returnObj->state = $state;
        $returnObj->index = $index;

        $node = array(
            'id' => 'foo',
            'name' => 'bar',
            'type' => $type,
        );

        $mock = $this->getMockBuilder('\ProductTemplateTreeApi')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $leaf = TestReflection::callProtectedMethod($mock, 'generateNewLeaf', [$node, $index]);

        $this->assertEquals($returnObj, $leaf);
    }

    public function generateNewLeafProvider()
    {
        return array(
            array(
                'category', 'closed',
                'product', '',
            ),
        );
    }

    /**
     * @covers ::getFilteredTreeData
     */
    public function testGetFilteredTreeData()
    {
        $filter = '%foo%';
        $unionFilter = "and name like ? ";

        $mock = $this->getMockBuilder('\ProductTemplateTreeApi')
            ->setMethods(['getTreeData'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())
            ->method('getTreeData')
            ->with($unionFilter, $unionFilter, $filter, $filter);

        TestReflection::callProtectedMethod($mock, 'getFilteredTreeData', ['foo']);
    }

    /**
     * @dataProvider getRootedTreeDataProvider
     * @covers ::getRootedTreeData
     */
    public function testGetRootededTreeData($root)
    {
        $union1Root = '';
        $union2Root = '';

        if ($root == null) {
            $union1Root = "and parent_id is null ";
            $union2Root = "and category_id is null ";
        } else {
            $union1Root = "and parent_id = ? ";
            $union2Root = "and category_id = ? ";
        }

        $mock = $this->getMockBuilder('\ProductTemplateTreeApi')
            ->setMethods(['getTreeData'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())
            ->method('getTreeData')
            ->with($union1Root, $union2Root, $root, $root);

        TestReflection::callProtectedMethod($mock, 'getRootedTreeData', [$root]);
    }

    public function getRootedTreeDataProvider()
    {
        return array(
            array('foo'),
            array(null),
        );
    }

    /**
     * @covers ::getTreeData
     */
    public function testGetTreeData()
    {
        $union1Filter = 'foo';
        $union2Filter = 'bar';
        $filter1 = 'foo1';
        $filter2 = 'bar1';

        $q = "select id, name, 'category' as type from product_categories " .
            "where deleted = 0 " .
            $union1Filter .
            "union all " .
            "select id, name, 'product' as type from product_templates " .
            "where deleted = 0 " .
            $union2Filter .
            "order by type, name";

        $mock = $this->getMockBuilder('\ProductTemplateTreeApi')
            ->setMethods(['getDBConnection', 'getDBInstance'])
            ->disableOriginalConstructor()
            ->getMock();

        $dbConnectionMock = $this->getMockBuilder('mockProductTemplateTreeApiTestDBConnection')
            ->setMethods(['prepare'])
            ->disableOriginalConstructor()
            ->getMock();

        $dbConnectionMock->expects($this->any())
            ->method('prepare')
            ->with($q)
            ->will($this->returnValue(new mockProductTemplateTreeApiTestDBConnection()));

        $mock->expects($this->any())
            ->method('getDBConnection')
            ->will($this->returnValue($dbConnectionMock));

        TestReflection::callProtectedMethod(
            $mock,
            'getTreeData',
            [$union1Filter, $union2Filter, $filter1, $filter2]
        );

        //because simply testing that a function is called with params isn't enough, we MUST assert something ಠ_ಠ
        $this->assertEquals(1, 1);
    }

    public function getTreeDataProvider()
    {
        return array(
            array('', '', null),
            array("and name like ? ", "and (pc.name like ? or pt.name like ?) ", 'foo'),
        );
    }

    /**
     * @dataProvider getTemplateTreeProvider
     * @covers ::getTemplateTree
     * @covers ::addLeaf
     */
    public function testGetTemplateTree($args, $filteredTreeCallCount, $rootedTreeCallCount, $index, $id, $data, $total)
    {
        //build tree data
        $treeData = [];
        for ($i=1; $i <= 100; $i++) {
            $treeData[] = array(
                'id' => $i,
                'name' => 'foo_' . $i,
                'type' => 'category',
            );
        }

        for ($i=101; $i <= 200; $i++) {
            $treeData[] = array(
                'id' => $i,
                'name' => 'bar_' . $i,
                'type' => 'product',
            );
        }

        $mock = $this->getMockBuilder('\ProductTemplateTreeApi')
            ->setMethods(['getFilteredTreeData', 'getRootedTreeData', 'getSugarConfig'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->exactly($filteredTreeCallCount))
            ->method('getFilteredTreeData')
            ->with(array_key_exists('filter', $args)? $args['filter']: '')
            ->will($this->returnValue($treeData));

        $mock->expects($this->exactly($rootedTreeCallCount))
            ->method('getRootedTreeData')
            ->with(array_key_exists('root', $args)? $args['root']: '')
            ->will($this->returnValue($treeData));

        $sugarConfigMock = $this->getMockBuilder('\SugarConfig')
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $sugarConfigMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue(20));
        
        $mock->expects($this->any())
            ->method('getSugarConfig')
            ->will($this->returnValue($sugarConfigMock));

        $results = $mock->getTemplateTree(new SugarApiProductTemplateTreeApiTestServiceMock(), $args);

        if ($data) {
            $this->assertEquals($id, $results['records'][0]->id);
            $this->assertEquals($index, $results['records'][0]->index);
        } else {
            $this->assertEquals([], $results['records']);
        }

        $this->assertEquals($total, count($results['records']));
    }

    public function getTemplateTreeProvider()
    {
        return array(
            array(
                array('filter' => 'foo'),
                1,
                0,
                0,
                1,
                true,
                20,
            ),
            array(
                array('root' => 'foo'),
                0,
                1,
                0,
                1,
                true,
                20,
            ),
            array(
                array('filter' => 'foo', 'offset' => 100),
                1,
                0,
                100,
                101,
                true,
                20,
            ),
            array(
                array('offset' => 201),
                0,
                1,
                100,
                101,
                false,
                0,
            ),
            array(
                array('max_num' => 50),
                0,
                1,
                0,
                1,
                true,
                20,
            ),
            array(
                array('max_num' => -1),
                0,
                1,
                0,
                1,
                true,
                20,
            ),
        );
    }
}

class mockProductTemplateTreeApiTestDBConnection
{
    public function prepare()
    {
    }
    public function execute()
    {
    }
    public function fetchAll()
    {
    }
}

class SugarApiProductTemplateTreeApiTestServiceMock extends \ServiceBase
{
    public function execute()
    {
    }

    protected function handleException(\Exception $exception)
    {
    }
}
