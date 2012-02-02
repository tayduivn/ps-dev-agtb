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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

 
require_once("modules/ModuleBuilder/Module/StudioModule.php");


/**
 * Bug #46196
 * Deleted field is not removed from subpanel for custom relationship
 *
 * @author dkroman@sugarcrm.com
 * @ticket 46196
 */
class Bug46196Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_backup = array();

	public function setUp()
    {
        $this->_backup = array(
            '_REQUEST' => $_REQUEST,
            'sugarCache' => sugarCache::$isCacheReset
        );

        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
    }
    
    public function tearDown() 
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
        rmdir_recursive('custom/modules/Accounts/metadata');
        rmdir_recursive('custom/modules/Accounts/Ext');
        rmdir_recursive('custom/modules/Accounts/language');

        $_REQUEST = $this->_backup['_REQUEST'];
        sugarCache::$isCacheReset = $this->_backup['sugarCache'];
        unset($GLOBALS['reload_vardefs']);
    }
    
    
    /**
     * Test tries to assert that field is not exist after removal it from subpanel
     * 
     * @group 46196
     */
    public function testRemoveCustomFieldFromSubpanelForCustomRelation()
    {
        $modules_exempt_from_availability_check = $GLOBALS['modules_exempt_from_availability_check'];

        $controller = new ModuleBuilderController;
        
        $module_name = 'Accounts';
        $_REQUEST['view_module'] = $module_name;
        $GLOBALS['modules_exempt_from_availability_check'] = array(
            $module_name => $module_name
        );

        $test_field_name = 'testfield_222222';
        $_REQUEST['name'] = $test_field_name;
        $_REQUEST['labelValue'] = 'testfield 222222';
        $_REQUEST['label'] = 'LBL_TESTFIELD_222222';
        $_REQUEST['type'] = 'varchar';

        $controller->action_SaveField();
        
        $_REQUEST['view_module'] = $module_name;
        $_REQUEST['relationship_type'] = 'many-to-many';
        $_REQUEST['lhs_module'] = $module_name;
        $_REQUEST['lhs_label'] = $module_name;
        $_REQUEST['rhs_module'] = $module_name;
        $_REQUEST['rhs_label'] = $module_name;
        $_REQUEST['lhs_subpanel'] = 'default';
        $_REQUEST['rhs_subpanel'] = 'default';
        
        $controller->action_SaveRelationship();
        
        $parser = ParserFactory::getParser('listview', $module_name, null, 'accounts_accounts');
        $field = $parser->_fielddefs[$test_field_name . '_c'];
        $parser->_viewdefs[$test_field_name . '_c'] = $field;
        $parser->handleSave(false);
        
        $_REQUEST['type'] = 'varchar';
        $_REQUEST['name'] = $test_field_name . '_c';
        $controller->action_DeleteField();

        $parser = ParserFactory::getParser('listview', $module_name, null, 'accounts_accounts');

        $_REQUEST['relationship_name'] = 'accounts_accounts';
        $controller->action_DeleteRelationship();

        $GLOBALS['modules_exempt_from_availability_check'] = $modules_exempt_from_availability_check;

        $this->assertArrayNotHasKey($test_field_name . '_c', $parser->_viewdefs);
    }
}