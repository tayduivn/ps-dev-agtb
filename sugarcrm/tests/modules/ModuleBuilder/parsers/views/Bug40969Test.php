<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once "modules/ModuleBuilder/parsers/views/ListLayoutMetaDataParser.php";
require_once 'modules/ModuleBuilder/parsers/views/DeployedMetaDataImplementation.php' ;

/**
 * Check ListLayoutMetaDataParser fills listviewdefs correctly for flex relate custom field to be displayed
 * in ListView layout.
 *
 * Field should contain:
 * 'related_fields' key - for data access (entity name)
 * 'id'                 - for entity id in link
 * 'dynamic_module'     - for entity module in link
 */
class Bug40969Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $vardefs =
        array(
            'name'         => array(
                                  'name'     => 'name',
                                  'vname'    => 'LBL_OPPORTUNITY_NAME',
                                  'type'     => 'name',
                                  'dbType'   => 'varchar',
                                  'required' => true,
                              ),
            'date_entered' => array(
                                  'name'  => 'date_entered',
                                  'vname' => 'LBL_DATE_ENTERED',
                                  'type'  => 'datetime',
                              ),
            'parent_name'  => array(
                                  'source'        => 'non-db',
                                  'name'          => 'parent_name',
                                  'vname'         => 'LBL_FLEX_RELATE',
                                  'type'          => 'parent',
                                  'options'       => 'parent_type_display',
                                  'type_name'     => 'parent_type',
                                  'id_name'       => 'parent_id',
                                  'parent_type'   => 'record_type_display',
                                  'id'            => 'Opportunitiesparent_name',
                                  'custom_module' => 'Opportunities',
                              ),
            'parent_id'    => array(
                                  'source'        => 'custom_fields',
                                  'name'          => 'parent_id',
                                  'vname'         => 'LBL_PARENT_ID',
                                  'type'          => 'id',
                                  'id'            => 'Opportunitiesparent_id',
                                  'custom_module' => 'Opportunities',
                              ),
            'parent_type'  => array(
                                  'required'      => false,
                                  'source'        => 'custom_fields',
                                  'name'          => 'parent_type',
                                  'vname'         => 'LBL_PARENT_TYPE',
                                  'type'          => 'parent_type',
                                  'dbType'        => 'varchar',
                                  'id'            => 'Opportunitiesparent_type',
                                  'custom_module' => 'Opportunities',
                              ),
        );

    /**
     * @var array
     */
    public $originalVardefs =
        array(
            'name'         => array(
                                  'width'   => 30,
                                  'label'   => 'LBL_LIST_OPPORTUNITIES_NAME',
                                  'link'    => true,
                                  'default' => true,
                              ),
            'dete_entered' => array(
                                  'width'   => 10,
                                  'label'   => 'LBL_DATE_ENTERED',
                                  'default' => true,
                              ),
        );

    public function setUp()
    {
        $_POST = array(
                     'group_0' => array('name', 'date_entered', 'parent_name'),
                 );
    }

    public function tearDown()
    {
        $_POST = array();
    }

    public function testCustomFlexFieldListViewDefs()
    {
        $methods = array('getFielddefs', 'getOriginalViewdefs', 'getViewdefs');

        // Mock ListLayoutMetaDataParser Meta Implementation and make it return test values
        $implementation = $this->getMock('DeployedMetaDataImplementation', $methods, array(), '', false);

        $implementation->expects($this->any())->method('getFielddefs')->will($this->returnValue($this->vardefs));
        $implementation->expects($this->any())->method('getOriginalViewdefs')->will($this->returnValue($this->originalVardefs));
        $implementation->expects($this->any())->method('getViewdefs')->will($this->returnValue($this->originalVardefs));

        $metaParser =  new Bug40969ListLayoutMetaDataParser($implementation, $this->vardefs);

        $metaParser->testBug40969();

        // Assert Flex Relate field contain required listview defs to be correctly displayed
        $this->assertArrayHasKey('parent_name', $metaParser->_viewdefs);
        $this->assertArrayHasKey('dynamic_module', $metaParser->_viewdefs['parent_name']);
        $this->assertArrayHasKey('id', $metaParser->_viewdefs['parent_name']);
        $this->assertArrayHasKey('link', $metaParser->_viewdefs['parent_name']);
        $this->assertTrue($metaParser->_viewdefs['parent_name']['link']);
        $this->assertArrayHasKey('related_fields', $metaParser->_viewdefs['parent_name']);
        $this->assertEquals(array('parent_id', 'parent_type'), $metaParser->_viewdefs['parent_name']['related_fields']);
    }

}

/**
 * Helper class to access protected "_populateFromRequest" method
 */
class Bug40969ListLayoutMetaDataParser extends ListLayoutMetaDataParser
{
    /**
     * @var DeployedMetaDataImplementation
     */
    public $implementation;

    public function __construct($implementation)
    {
        $this->implementation = $implementation;

        $this->_viewdefs = array_change_key_case($this->implementation->getViewdefs());
        $this->_fielddefs = $this->implementation->getFielddefs() ;
    }

    public function testBug40969()
    {
        return $this->_populateFromRequest();
    }

}
