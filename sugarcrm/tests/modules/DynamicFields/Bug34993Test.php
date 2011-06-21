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
 
require_once("modules/Accounts/Account.php");

class Bug34993Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_tablename;
    private $_old_installing;

    public function setUp()
    {
        $this->accountMockBean = $this->getMock('Account' , array('hasCustomFields'));
        $this->_tablename = 'test' . date("YmdHis");
        if ( isset($GLOBALS['installing']) )
        {
            $this->_old_installing = $GLOBALS['installing'];
        }
        $GLOBALS['installing'] = true;

        $GLOBALS['db']->createTableParams($this->_tablename . '_cstm',
            array(
                'id_c' => array (
                    'name' => 'id_c',
                    'type' => 'id',
                    ),                 
                ),
            array()
            );
        $GLOBALS['db']->query("INSERT INTO {$this->_tablename}_cstm (id_c) VALUES ('12345')");
        
        //Safety check in case the previous run had failed
        $GLOBALS['db']->query("DELETE FROM fields_meta_data WHERE id in ('Accountsbug34993_test_c', 'Accountsbug34993_test2_c')");
    }

    public function tearDown()
    {
        $GLOBALS['db']->dropTableName($this->_tablename . '_cstm');
        $GLOBALS['db']->query("DELETE FROM fields_meta_data WHERE id in ('Accountsbug34993_test_c', 'Accountsbug34993_test2_c'");
        if ( isset($this->_old_installing) ) {
            $GLOBALS['installing'] = $this->_old_installing;
        } else {
            unset($GLOBALS['installing']);
        }
    }

    public function testCustomFieldDefaultValue()
    {
    	require_once('modules/DynamicFields/templates/Fields/TemplateText.php');
    	require_once('modules/DynamicFields/DynamicField.php');
    	require_once('modules/DynamicFields/FieldCases.php');
    	
    	//Simulate create a custom text field with a default value set to 123
    	$templateText = get_widget('varchar');
    	$templateText->type = 'varchar';
    	$templateText->view = 'edit';
    	$templateText->label = 'CUSTOM TEST';
    	$templateText->name = 'bug34993_test';
    	$templateText->size = 20;
    	$templateText->len = 255;
    	$templateText->required = false;
    	$templateText->default = '123';
    	$templateText->default_value = '123';
    	$templateText->comment = '';
    	$templateText->audited = 0;
    	$templateText->massupdate = 0;
    	$templateText->importable = true;
    	$templateText->duplicate_merge = 0;
    	$templateText->reportable = 1;
        $templateText->ext1 = NULL;
        $templateText->ext2 = NULL;
        $templateText->ext3 = NULL;
        $templateText->ext4 = NULL;    	
    	
        $bean = $this->accountMockBean;
        $bean->custom_fields = new DynamicField($bean->module_dir);
        $bean->custom_fields->setup($bean);
   
        $bean->expects($this->any())
             ->method('hasCustomFields')
             ->will($this->returnValue(true));
        $bean->table_name = $this->_tablename;
        $bean->id = '12345';
        $bean->custom_fields->addFieldObject($templateText);        
        $bean->custom_fields->retrieve();
        $this->assertEquals($bean->id_c, '12345', "Assert that the custom table exists");
        $this->assertEquals($bean->bug34993_test_c, NULL, "Assert that the custom text field has a default value set to NULL");
        
        
    	//Simulate create a custom text field with a default value set to 123
    	$templateText = get_widget('enum');
    	$templateText->type = 'enum';
    	$templateText->view = 'edit';
    	$templateText->label = 'CUSTOM TEST2';
    	$templateText->name = 'bug34993_test2';
    	$templateText->size = 20;
    	$templateText->len = 255;
    	$templateText->required = false;
    	$templateText->default = '123';
    	$templateText->default_value = '123';
    	$templateText->comment = '';
    	$templateText->audited = 0;
    	$templateText->massupdate = 0;
    	$templateText->importable = true;
    	$templateText->duplicate_merge = 0;
    	$templateText->reportable = 1;
        $templateText->ext1 = 'account_type_dom';
        $templateText->ext2 = NULL;
        $templateText->ext3 = NULL;
        $templateText->ext4 = NULL;    	
    	
        $bean = $this->accountMockBean;
        $bean->custom_fields = new DynamicField($bean->module_dir);
        $bean->custom_fields->setup($bean);
   
        $bean->expects($this->any())
             ->method('hasCustomFields')
             ->will($this->returnValue(true));
        $bean->table_name = $this->_tablename;
        $bean->id = '12345';
        $bean->custom_fields->addFieldObject($templateText);        
        $bean->custom_fields->retrieve();
        $this->assertEquals($bean->id_c, '12345', "Assert that the custom table exists");
        $this->assertEquals($bean->bug34993_test2_c, NULL, "Assert that the custom enum field has a default value set to NULL");        
    }
}
