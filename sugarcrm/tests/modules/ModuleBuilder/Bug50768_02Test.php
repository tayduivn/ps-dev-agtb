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

require_once 'modules/DynamicFields/templates/Fields/TemplateCurrency.php';
require_once("modules/ModuleBuilder/controller.php");

/**
 * Bug #50768
 * in Studio once set the visibility function for currency field will miss the currency_Id
 *
 * @author asokol@sugarcrm.com
 * @ticket 50768
 */

class Bug50768_02Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $targetModule = "Accounts";
    protected $currencyFieldDef1 = array(
        "action" => "saveField",
        "comments" => "",
        "default" => "",
        "dependency" => "",
        "dependency_display" => "",
        "duplicate_merge" => "0",
        "enforced" => "false",
        "formula" => "",
        "formula_display" => "",
        "help" => "",
        "importable" => "true",
        "is_update" => "true",
        "labelValue" => "test_cur_c1",
        "label" => "LBL_TEST_CUR_1",
        "new_dropdown" => "",
        "reportableCheckbox" => "1",
        "reportable" => "1",
        "to_pdf" => "true",
        "type" => "currency",
        "name" => "c1",
        "module" => "ModuleBuilder",
        "view_module" => "Accounts",
    );

    protected $currencyFieldDef2 = array(
        "action" => "saveField",
        "comments" => "",
        "default" => "",
        "dependency" => "",
        "dependency_display" => "",
        "duplicate_merge" => "0",
        "enforced" => "false",
        "formula" => "",
        "formula_display" => "",
        "help" => "",
        "importable" => "true",
        "is_update" => "true",
        "labelValue" => "test_cur_c2",
        "label" => "LBL_TEST_CUR_2",
        "new_dropdown" => "",
        "reportableCheckbox" => "1",
        "reportable" => "1",
        "to_pdf" => "true",
        "type" => "currency",
        "name" => "c2",
        "module" => "ModuleBuilder",
        "view_module" => "Accounts",
    );

    public function setUp()
    {

        $this->markTestIncomplete("This test breaks others tests on 644 on CI.  Disabling for sanity check");

        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);

        $mbc = new ModuleBuilderController();
        //Create the new Fields
        $_REQUEST = $this->currencyFieldDef1;
        $mbc->action_SaveField();
        $_REQUEST = $this->currencyFieldDef2;
        $mbc->action_SaveField();

    }

    public function tearDown()
    {
      /*  $mbc = new ModuleBuilderController();
        $this->currencyFieldDef1['name'] = 'c1_c';
        $_REQUEST = $this->currencyFieldDef1;
        $mbc->action_DeleteField();
        $this->currencyFieldDef2['name'] = 'c2_c';
        $_REQUEST = $this->currencyFieldDef2;
        $mbc->action_DeleteField();

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        unset($GLOBALS['current_user']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['app_list_strings']);
        unset($_REQUEST);*/

    }

    /**
     * Test checks if currency_id field remains with currency_id type
     * @group 50768
     */
    public function testCurrencyIdType()
    {
        $cType = '';
        $bean = BeanFactory::getBean($this->targetModule);
        if(!empty($bean))
        {
            $fieldDefs = $bean->field_defs;
            if(isset($fieldDefs['currency_id']))
            {
                $cType = $fieldDefs['currency_id']['type'];
            }
        }

        $this->assertEquals($cType, 'currency_id');
    }

    /**
     * Test checks if there is 1 currency_id field for 2 currency fields
     * @group 50768
     */
    public function testSettedCurrencyIdField()
    {
        $count = 0;
        $query = "SELECT * FROM fields_meta_data WHERE custom_module='Accounts' AND type='currency_id' AND deleted = 0";
        $result = $GLOBALS['db']->query ( $query );
        while ( $row = $GLOBALS['db']->fetchByAssoc ( $result ) ) {
            $count++;
        }
        $this->assertEquals($count, 1);
    }
}
