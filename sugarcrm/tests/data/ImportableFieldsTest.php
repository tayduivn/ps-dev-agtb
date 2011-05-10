<?php
/********************************************************************************
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

require_once('data/SugarBean.php');

class ImportableFieldsTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $myBean;

	public function setUp()
	{
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        
        $this->myBean = new SugarBean();
        
        $this->myBean->field_defs = array( 
            'id' => array('name' => 'id', 'vname' => 'LBL_ID', 'type' => 'id', 'required' => true, ),
            'name' => array('name' => 'name', 'vname' => 'LBL_NAME', 'type' => 'varchar', 'len' => '255', 'importable' => 'required', ),
            'bool_field' => array('name' => 'bool_field', 'vname' => 'LBL_BOOL_FIELD', 'type' => 'bool', 'importable' => false, ),
            'int_field' => array('name' => 'int_field', 'vname' => 'LBL_INT_FIELD', 'type' => 'int', ),
            'autoinc_field' => array('name' => 'autoinc_field', 'vname' => 'LBL_AUTOINC_FIELD', 'type' => 'true', 'auto_increment' => true, ),
            'float_field' => array('name' => 'float_field', 'vname' => 'LBL_FLOAT_FIELD', 'type' => 'float', 'precision' => 2, ),
            'date_field' => array('name' => 'date_field', 'vname' => 'LBL_DATE_FIELD', 'type' => 'date', ),
            'time_field' => array('name' => 'time_field', 'vname' => 'LBL_TIME_FIELD', 'type' => 'time', 'importable' => 'false', ),
            //BEGIN SUGARCRM flav!=com ONLY
            'image_field' => array('name' => 'image_field', 'vname' => 'LBL_IMAGE_FIELD', 'type' => 'image', ),
            //END SUGARCRM flav!=com ONLY
            'datetime_field' => array('name' => 'datetime_field', 'vname' => 'LBL_DATETIME_FIELD', 'type' => 'datetime', ),
            'link_field1' => array('name' => 'link_field1', 'type' => 'link', ),
            'link_field2' => array('name' => 'link_field1', 'type' => 'link', 'importable' => true, ),
            'link_field3' => array('name' => 'link_field1', 'type' => 'link', 'importable' => 'true', ),
        );

	}

	public function tearDown()
	{
		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($this->time_date);
	}
	
	/**
     * @ticket 31397
     */
	public function testImportableFields()
	{
        $fields = array(
            'id',
            'name',
            'int_field',
            'float_field',
            'date_field',
            'datetime_field',
            'link_field2',
            'link_field3',
            );
        $this->assertEquals(
            $fields,
            array_keys($this->myBean->get_importable_fields())
            );
    }
    
    /**
     * @ticket 31397
     */
	public function testImportableRequiredFields()
	{
        $fields = array(
            'name',
            );
        $this->assertEquals(
            $fields,
            array_keys($this->myBean->get_import_required_fields())
            );
    }
}