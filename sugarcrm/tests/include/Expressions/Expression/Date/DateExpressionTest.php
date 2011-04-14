<?php
//FILE SUGARCRM flav=pro ONLY
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
//require_once("include/Expressions/Expression/Date/DateExpression.php");
require_once("include/Expressions/Expression/Parser/Parser.php");

class DateExpressionTest extends Sugar_PHPUnit_Framework_TestCase
{
    static $createdBeans = array();

	public static function setUpBeforeClass()
	{
	    require('include/modules.php');
	    $GLOBALS['beanList'] = $beanList;
	    $GLOBALS['beanFiles'] = $beanFiles;
	    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	    $GLOBALS['current_user']->setPreference('timezone', "America/Los_Angeles");
	    $GLOBALS['current_user']->setPreference('datef', "m/d/Y");
		$GLOBALS['current_user']->setPreference('timef', "h.iA");
	}

	public static function tearDownAfterClass()
	{
	    foreach(self::$createdBeans as $bean)
        {
            $bean->mark_deleted($bean->id);
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	    unset($GLOBALS['current_user']);
	    unset($GLOBALS['beanList']);
	    unset($GLOBALS['beanFiles']);
	}

	public function testAddDays()
	{
	    $task = new Task();
	    $task->date_due = '2001-01-01 11:45:00';
        $expr = 'addDays($date_due, 7)';
        $result = Parser::evaluate($expr, $task)->evaluate();
	    $this->assertInstanceOf("DateTime", $result);
	    $expect = TimeDate::getInstance()->fromDb('2001-01-01 11:45:00')->get('+ 7 days')->asDb();
        $this->assertEquals($expect, TimeDate::getInstance()->asDb($result));
	}

	public function testDayOfWeek()
	{
	    $task = new Task();
	    $task->date_due = '2011-01-10 01:00:00'; // this is Monday in GMT but Sunday in PST
	    $expr = 'dayofweek($date_due)';
	    $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals(0, $result);

        $task->date_due = '2011-01-10 21:00:00'; // this is Monday in both timezones
	    $expr = 'dayofweek($date_due)';
	    $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals(1, $result);
	}

	public function testMonthOfYear()
	{
	    $task = new Task();
	    $task->date_due = '2011-01-09 21:00:00';
	    $expr = 'monthofyear($date_due)';
	    $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals(1, $result);

        $task->date_due = '2011-03-01 01:00:00'; // this is February in PST
	    $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals(2, $result);
	}

	public function testDefineDate()
	{
	    $task = new Task();
	    $expr = 'date($name)';
	    $timedate = TimeDate::getInstance();

	    $task->name = '3/18/2011';
	    $result = Parser::evaluate($expr, $task)->evaluate();
	    $this->assertInstanceOf("DateTime", $result);
	    $this->assertEquals($timedate->asUserDate($timedate->fromUserDate('3/18/2011')), $timedate->asUserDate($result));

	}

	public function testNow()
	{
	    $task = new Task();
	    $expr = 'now()';
	    $result = Parser::evaluate($expr, $task)->evaluate();
	    $this->assertInstanceOf("DateTime", $result);
	    $this->assertEquals(TimeDate::getInstance()->getNow(true)->format('r'), $result->format('r'));
	}

	public function testToday()
	{
	    $task = new Task();
	    $expr = 'today()';
	    $result = Parser::evaluate($expr, $task)->evaluate();
	    $this->assertInstanceOf("DateTime", $result);
	    $this->assertEquals(TimeDate::getInstance()->getNow(true)->format('Y-m-d'), $result->format('Y-m-d'));
	}

	public function testDaysUntil()
	{
	    $task = new Task();
	    $task->date_due = gmdate('Y-m-d H:i:s', time()+86400*5);;
        $expr = 'daysUntil($date_due)';
        $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals(5, $result);
	}

	public function testBeforeAfter()
	{
	    $task = new Task();
	    $task->date_start = '2011-01-01 21:00:00';
	    $task->date_due = '2011-01-09 01:00:00';

	    $expr = 'isBefore($date_start, $date_due)';
        $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals($result, "true");

        $expr = 'isAfter($date_start, $date_due)';
        $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals($result, "false");

        $expr = 'isBefore($date_due, $date_start)';
        $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals($result, "false");

        $expr = 'isAfter($date_due, $date_start)';
        $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals($result, "true");
	}

	public function testIsValidDate()
	{
	    $task = new Task();
	    $timedate = TimeDate::getInstance();
	    $task->name = $timedate->to_display_date_time('2011-01-01 21:00:00');
	    $expr = 'isValidDate($name)';
        $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals($result, "true");

        $task->name = '42';
	    $expr = 'isValidDate($name)';
        $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals($result, "false");

        $task->name = 'Chuck Norris';
	    $expr = 'isValidDate($name)';
        $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals($result, "false");

        $task->name = '2011-01-01 21:00:00';
	    $expr = 'isValidDate($name)';
        $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals($result, "false");
	}

	public function testBadDates()
	{
	    $task = new Task();
        $task->name = 'Chuck Norris';
	    $expr = 'date($name)';
        try {
            $result = Parser::evaluate($expr, $task)->evaluate();
	        $this->assertTrue(false, "Incorrecty converted '{$task->name }' to date $result");
        } catch (Exception $e){
            $this->assertContains("invalid value to date", $e->getMessage());
        }
        $task->date_due = 'Chuck Norris';
	    $expr = 'addDays($date_due, 3)';
	    try {
            $result = Parser::evaluate($expr, $task)->evaluate();
	        $this->assertTrue(false, "Incorrecty converted '{$task->date_due }' to date $result");
        } catch (Exception $e){
            $this->assertContains("invalid value to date", $e->getMessage());
        }

	    $expr = 'addDays($date_start, 3)'; // not setting the value
	    try {
            $result = Parser::evaluate($expr, $task)->evaluate();
	        $this->assertTrue(false, "Incorrecty converted empty string to date $result");
        } catch (Exception $e){
            $this->assertContains("attempt to get date from empty field", $e->getMessage());
        }
	}

	/**
	 * Test autoconverting strings to dates
	 */
	public function testConvert()
	{
	    $task = new Task();
	    $timedate = TimeDate::getInstance();
	    $now = $timedate->getNow();
	    $task->name = $timedate->asUser($now);
	    $expr = 'addDays($name, 3)';
	    $result = Parser::evaluate($expr, $task)->evaluate();
	    $this->assertInstanceOf("DateTime", $result);
	    $this->assertEquals($timedate->asUser($timedate->getNow(true)->get("+3 days")), $timedate->asUser($result));
	}

}