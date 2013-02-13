<?php
//FILE SUGARCRM flav=pro ONLY
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

require_once 'modules/DynamicFields/FieldCases.php';

/**
 * @ticket 59155
 */
class Bug59155Test extends Sugar_PHPUnit_Framework_TestCase
{
    private static $custom_field_def = array(
        'formula'     => 'related($accounts,"name")',
        'name'        => 'bug_59155',
        'type'        => 'text',
        'label'       => 'LBL_CUSTOM_FIELD',
        'module'      => 'ModuleBuilder',
        'view_module' => 'Cases',
    );

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, 1));

        $mbc = new ModuleBuilderController();
        $_REQUEST = self::$custom_field_def;
        $mbc->action_SaveField();

        VardefManager::refreshVardefs('Cases', 'Case');
    }

    public static function tearDownAfterClass()
    {
        $mbc = new ModuleBuilderController();

        $custom_field_def = self::$custom_field_def;
        $custom_field_def['name'] .= '_c';
        $_REQUEST = $custom_field_def;
        $mbc->action_DeleteField();

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        $_REQUEST = array();
        SugarCache::$isCacheReset = false;

        SugarTestHelper::tearDown();
    }

    public function testCaseCalcFieldIsConsidered()
    {
        $account = new Bug59155Test_Account();
        $fields = $account->get_fields_influencing_linked_bean_calc_fields('cases');
        $this->assertContains('name', $fields);
    }
}

class Bug59155Test_Account extends Account
{
    public function get_fields_influencing_linked_bean_calc_fields($linkName)
    {
        return parent::get_fields_influencing_linked_bean_calc_fields($linkName);
    }
}
