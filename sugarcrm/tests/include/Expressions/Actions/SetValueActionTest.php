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

require_once("include/Expressions/Dependency.php");
require_once("include/Expressions/Trigger.php");
require_once("include/Expressions/Expression/Parser/Parser.php");
require_once("include/Expressions/Actions/ActionFactory.php");

class SetValueActionTest extends Sugar_PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
	{
	    require('include/modules.php');
	    $GLOBALS['beanList'] = $beanList;
	    $GLOBALS['beanFiles'] = $beanFiles;
	    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	}

	public static function tearDownAfterClass()
	{
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	    unset($GLOBALS['current_user']);
	    unset($GLOBALS['beanList']);
	    unset($GLOBALS['beanFiles']);
	}

	public function testSetValues()
	{
	    $task = new Task();

        //Test Date value
        $task->date_due = '2001-01-10 11:45:00';
        $target = "date_start";
        $expr = 'addDays($date_due, -7)';
        $action = ActionFactory::getNewAction("SetValue", array("target" => $target,"value" => $expr));
        $action->fire($task);
        $this->assertEquals($task->$target, TimeDate::getInstance()->fromDb('2001-01-10 11:45:00')->get('- 7 days')->asDb());

        //Test string value
        $target = "name";
        $expr = 'concat("Hello", " ", "World")';
        $action = ActionFactory::getNewAction("SetValue", array("target" => $target,"value" => $expr));
        $action->fire($task);
        $this->assertEquals($task->$target, "Hello World");


        //Test numeric value
        $target = "name";
        $expr = 'ceiling(pi)';
        $action = ActionFactory::getNewAction("SetValue", array("target" => $target,"value" => $expr));
        $action->fire($task);
        $this->assertEquals($task->$target, 4);

	}
}