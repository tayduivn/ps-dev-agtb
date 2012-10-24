<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
 
require_once('modules/DynamicFields/FieldCases.php');

/**
 * @group DynamicFieldsCurrencyTests
 */

class DynamicFieldsCurrencyTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_modulename = 'Accounts';
    private $_originaldbType = '';
    private $field;
    
    public function setUp()
    {
        // Set Original Global dbType
        $this->_originaldbType = $GLOBALS['db']->dbType;
        
    	$this->field = get_widget('currency');
        $this->field->id = $this->_modulename.'foofighter_c';
        $this->field->name = 'foofighter_c';
        $this->field->vanme = 'LBL_Foo';
        $this->field->comments = NULL;
        $this->field->help = NULL;
        $this->field->custom_module = $this->_modulename;
        $this->field->type = 'currency';
        $this->field->len = 18;
        $this->field->precision = 6;
        $this->field->required = 0;
        $this->field->default_value = NULL;
        $this->field->date_modified = '2010-12-22 01:01:01';
        $this->field->deleted = 0;
        $this->field->audited = 0;
        $this->field->massupdate = 0;
        $this->field->duplicate_merge = 0;
        $this->field->reportable = 1;
        $this->field->importable = 'true';
        $this->field->ext1 = NULL;
        $this->field->ext2 = NULL;
        $this->field->ext3 = NULL;
        $this->field->ext4 = NULL;
    }
    
    public function tearDown()
    {
        // Reset Original Global dbType
        $GLOBALS['db']->dbType = $this->_originaldbType;
    }
    
    public function testCurrencyDbType()
    {
        $type = 'decimal';
        //BEGIN SUGARCRM flav=ent ONLY
        if ($GLOBALS['db']->dbType == 'oci8')
        {
            $type = 'number';
        }
        //END SUGARCRM flav=ent ONLY
        $this->field->len = NULL;
        $dbTypeString = $this->field->get_db_type();
        $this->assertRegExp('/' . $type . ' *\(/', $dbTypeString);
        $dbTypeString = $this->field->get_db_type();
        $this->field->len = 20;
        $this->assertRegExp('/' . $type . ' *\(/', $dbTypeString);
    }
}
