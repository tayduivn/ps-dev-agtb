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
require_once 'data/Relationships/SugarRelationship.php';

/**
 * @ticket 62026
 * @author avucinic
 */
class Bug62026Test extends Sugar_PHPUnit_Framework_TestCase
{
    private static $custom_field_def = array(
        'calculated'  => 'true',
        'formula'     => 'count($tasks)',
        'name'        => 'bug62026',
        'type'        => 'text',
        'enforced'    => 'true',
        'label'       => 'Bug 62026 Custom Count',
        'module'      => 'ModuleBuilder',
        'view_module' => 'Accounts',
    );

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, 1));

        // Create the custom field
        $mbc = new ModuleBuilderController();
        $_REQUEST = self::$custom_field_def;
        $mbc->action_saveField();
        // Update field name, all custom field have _c appended
        self::$custom_field_def['name'] .= '_c';

        // Refresh vardefs (for related_calc_fields)
        VardefManager::refreshVardefs('Accounts', 'Account');
        VardefManager::refreshVardefs('Tasks', 'Task');
    }

    public static function tearDownAfterClass()
    {
        $mbc = new ModuleBuilderController();

        // Delete the custom field
        $_REQUEST = self::$custom_field_def;
        $mbc->action_DeleteField();

        // Clear created beans
        SugarTestTaskUtilities::removeAllCreatedTasks();
        SugarTestAccountUtilities::removeAllCreatedAccounts();

        $_REQUEST = array();
        SugarCache::$isCacheReset = false;

        SugarTestHelper::tearDown();
    }

    public function testCalculatedFieldCount()
    {
        // Create account
        $account = SugarTestAccountUtilities::createAccount();
        $account = $account->retrieve($account->id);
        // Create task
        $task = new Task();

        // Pick the custom field name for usage
        $field = self::$custom_field_def['name'];

        // Link to account when creating new task using parent_type/id
        $task->name = 'Bug 62026';
        $task->parent_id = $account->id;
        $task->parent_name = $account->name;
        $task->parent_type = $account->module_dir;
        $task->save();
        $account = $account->retrieve($account->id);
        $this->assertEquals(1, $account->$field, 'Create account with parent_id/type does not update count()');

        // Save the ID for removal after test is done
        SugarTestTaskUtilities::setCreatedTask(array($task->id));

        // Unlink account by updating parent_id/type
        $task = $task->retrieve($task->id);
        $task->parent_id = '';
        $task->parent_type = 'Leads';
        $task->save();
        $account = $account->retrieve($account->id);
        $this->assertEquals(0, $account->$field, 'Unlink account by blanking parent_id/type does not update count()');

        // Load accounts relationship
        $task->load_relationship('accounts');

        // Check linking task with account
        $task->accounts->add($account);
        SugarRelationship::resaveRelatedBeans();
        $account = $account->retrieve($account->id);
        $this->assertEquals(1, $account->$field, 'Add relationship does not update count()');

        // Check unlinking task with account
        $task->accounts->delete($account);
        SugarRelationship::resaveRelatedBeans();
        $account = $account->retrieve($account->id);
        $this->assertEquals(0, $account->$field, 'Delete relationship does not update count()');

        // Link to account by updating parent_id/type
        $task = $task->retrieve($task->id);
        $task->parent_id = $account->id;
        $task->parent_type = $account->module_dir;
        $task->save();
        $account = $account->retrieve($account->id);
        $this->assertEquals(1, $account->$field, 'Update parent_id/type does not update count()');

        // Check deletion of task
        $task->retrieve($task->id);
        $task->mark_deleted($task->id);
        $account = $account->retrieve($account->id);
        $this->assertEquals(0, $account->$field, 'Bean delete does not update count()');
    }
}
