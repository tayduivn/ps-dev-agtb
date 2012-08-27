<?php
//FILE SUGARCRM flav=pro ONLY
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

require_once('include/nusoap/nusoap.php');
require_once('tests/service/SOAPTestCase.php');

/**
 * @group bug43696
 */
class Bug43696Test extends SOAPTestCase
{
    private $_tsk = null;

	public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';
        parent::setUp();
        $this->_tsk = new Task();
        $this->_tsk->name = "Unit Test";
        $this->_tsk->assigned_user_id = $GLOBALS['current_user']->id;
        $this->_tsk->save();
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM tasks WHERE id = '{$this->_tsk->id}'");
        parent::tearDown();
    }

    /**
     * We want to make sure that a user can sync their own tasks.
     * only sync their own tasks
     * @return void
     */
    public function testSyncMyTasks()
    {
        $timedate = TimeDate::getInstance();
        $this->_login();

        $result = $this->_soapClient->call('sync_get_modified_relationships',
            array(
                'session' => $this->_sessionId,
                'module' => 'Users',
                'related_module' => 'Tasks',
                'from_date' => $timedate->getNow()->modify("- 2 minutes")->asDb(),
                'to_date' => $timedate->getNow()->asDb(),
                'offset' => 0,
                'max_results' => 100,
                'deleted' => 0,
                'module_id' => $GLOBALS['current_user']->id,
                'select_fields' => array('id', 'date_modified', 'deleted', 'name'),
                'id' => array(),
                'relationship_name' => 'tasks_assigned_user',
                'deletion_date' => $timedate->getNow()->modify("- 2 minutes")->asDb(),
                'php_serialize' => 0
            )
        );
        $this->assertContains($this->_tsk->id, base64_decode($result['entry_list']), 'The Result does not contain the Task Id');

    }

    /**
     * We want to make sure that even though the user is an admin they should not sync all tasks and should
     * only sync their own tasks.
     * @return void
     */
    public function testDontSyncOtherTasks()
    {
        $timedate = TimeDate::getInstance();

        //change the user to an admin
        $GLOBALS['current_user']->is_admin = 1;
        $GLOBALS['current_user']->save();

        //change the assigned user to another user
        $this->_tsk->assigned_user_id = 1;
        $this->_tsk->save();

        $this->_login();
        $result = $this->_soapClient->call('sync_get_modified_relationships',
            array(
                'session' => $this->_sessionId,
                'module' => 'Users',
                'related_module' => 'Tasks',
                'from_date' => $timedate->getNow()->modify("- 2 minutes")->asDb(),
                'to_date' => $timedate->getNow()->asDb(),
                'offset' => 0,
                'max_results' => 100,
                'deleted' => 0,
                'module_id' => $GLOBALS['current_user']->id,
                'select_fields' => array('id', 'date_modified', 'deleted', 'name'),
                'id' => array(),
                'relationship_name' => 'tasks_assigned_user',
                'deletion_date' => $timedate->getNow()->modify("- 2 minutes")->asDb(),
                'php_serialize' => 0
            )
        );
        $this->assertNotContains($this->_tsk->id, base64_decode($result['entry_list']), 'The Result should not contain the Task Id');

    }
}
