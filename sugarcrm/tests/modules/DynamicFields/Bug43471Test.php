<?php
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/**
 * @ticket 43471
 */
class Bug43471Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_tablename;
    private $_old_installing;
    
    public function setUp()
    {
        $this->accountMockBean = $this->getMock('TestBean');
        $this->_tablename = 'test' . date("YmdHis");
    }
    
    public function tearDown()
    {
    }
    
    public function testDynamicFieldsRepairCustomFields()
    {
        $bean = $this->accountMockBean;

        /** @var $df DynamicField */
        $df = $this->getMock('DynamicField', array('createCustomTable'));
        $bean->table_name = $this->_tablename;
        $bean->field_defs = array (
              'id' =>
              array (
                'name' => 'id',
                'vname' => 'LBL_ID',
                'type' => 'id',
                'required' => true,
                'reportable' => true,
                'comment' => 'Unique identifier',
              ),
              'name' =>
              array (
                'name' => 'name',
                'type' => 'name',
                'dbType' => 'varchar',
                'vname' => 'LBL_NAME',
                'len' => 150,
                'comment' => 'Name of the Company',
                'unified_search' => true,
                'audited' => true,
                'required' => true,
                'importable' => 'required',
                'merge_filter' => 'selected',
              ),
              'FooBar_c' =>
              array (
                'required' => false,
                'source' => 'custom_fields',
                'name' => 'FooBar_c',
                'vname' => 'LBL_FOOBAR',
                'type' => 'varchar',
                'massupdate' => '0',
                'default' => NULL,
                'comments' => 'LBL_FOOBAR_COMMENT',
                'help' => 'LBL_FOOBAR_HELP',
                'importable' => 'true',
                'duplicate_merge' => 'disabled',
                'duplicate_merge_dom_value' => '0',
                'audited' => false,
                'reportable' => true,
                'calculated' => false,
                'len' => '255',
                'size' => '20',
                'id' => 'AccountsFooBar_c',
                'custom_module' => 'Accounts',
              ),
            );
        $df->setup($bean);
        $df->expects($this->any())
                ->method('createCustomTable')
                ->will($this->returnValue(null));

        $helper = $this->getMock('MysqliHelper');
        $helper->expects($this->any())
                ->method('get_columns')
                ->will($this->returnValue(array(
                'foobar_c' => array (
                    'name' => 'FooBar_c',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                )));
        // set the new db helper
        $GLOBALS['db']->helper = $helper;

        $repair = $df->repairCustomFields(false);
        $this->assertEquals("", $repair);

        // reset the db helper
        $GLOBALS['db']->helper = null;
    }
}


// test bean class
require_once("include/SugarObjects/templates/company/Company.php");

// Account is used to store account information.
class TestBean extends Company {
}
