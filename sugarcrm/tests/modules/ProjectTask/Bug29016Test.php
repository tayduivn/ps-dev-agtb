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


require_once "modules/ProjectTask/ProjectTask.php";
require_once "include/utils.php";

class Bug29016Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $rid;
    /**
     * @var ProjectTask
     */
    private $task;

    /**
     * @var User
     */
    private $user;

    public function setUp()
    {
       $GLOBALS['current_user'] = $this->user = SugarTestUserUtilities::createAnonymousUser();
       
        $this->task = new ProjectTask();
        $this->task->resource_id = $this->user->id;
        $this->rid = create_guid();
        $GLOBALS['db']->query(sprintf("insert into project_resources (id, date_modified, modified_user_id, created_by, resource_id, resource_type) values ('%s', NOW(), '%s', '%s', '%s', 'Users')",
                                      $this->rid, $this->user->id, $this->user->id, $this->user->id));
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

        $GLOBALS['db']->query(sprintf("delete from project_task where id='%s'", $this->task->id));
        $GLOBALS['db']->query(sprintf("delete from project_resource where id='%s'", $this->rid));
    }


    public function testResourceName()
    {
        $this->task->fill_in_additional_detail_fields();
        $this->assertEquals($this->task->getResourceName(), $this->user->full_name);
        $this->assertEquals($this->task->resource_name, $this->user->full_name);
    }

}