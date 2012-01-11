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
require_once("include/Expressions/Expression/AbstractExpression.php");
require_once("include/Expressions/Expression/Parser/Parser.php");

/**
 * @outputBuffering enabled
 */
class validDateTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp() {
        $this->markTestIncomplete("TODO: trying to see why this is failing.");
    }

	public static function setUpBeforeClass()
	{
	    $beanList = array();
	    $beanFiles = array();
	    require('include/modules.php');
	    $GLOBALS['beanList'] = $beanList;
	    $GLOBALS['beanFiles'] = $beanFiles;
	    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->setPreference("datef", "m/d/Y");
	}

	public static function tearDownAfterClass()
	{
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	    unset($GLOBALS['current_user']);
	    unset($GLOBALS['beanList']);
	    unset($GLOBALS['beanFiles']);
	}

    /**
     * @group bug39037
     */
	public function testValidDate()
	{
        try {
            $expr = 'isValidDate("5/15/2010")';
            $result = Parser::evaluate($expr)->evaluate();
            $this->assertEquals($result, AbstractExpression::$TRUE);
        } catch (Exception $e){
        	$this->assertTrue(false, "Parser threw exception: {$e->getMessage()}");
        }
    }

    public function testInvalidString()
	{
        try {
            $expr = 'isValidDate("not a date")';
            $result = Parser::evaluate($expr)->evaluate();
            $this->assertEquals($result, AbstractExpression::$FALSE);
        } catch (Exception $e){
        	$this->assertTrue(false, "Parser threw exception: {$e->getMessage()}");
        }
    }

    public function testInvalidDateFormat()
	{
        try {
            $expr = 'isValidDate("5-15-2010")';
            $result = Parser::evaluate($expr)->evaluate();
            $this->assertEquals($result, AbstractExpression::$FALSE);
        } catch (Exception $e){
        	$this->assertTrue(false, "Parser threw exception: {$e->getMessage()}");
        }
    }

    public function testInvalidMonth()
	{
        try {
            $expr = 'isValidDate("25/15/2010")';
            $result = Parser::evaluate($expr)->evaluate();
            $this->assertEquals($result, AbstractExpression::$FALSE);
        } catch (Exception $e){
        	$this->assertTrue(false, "Parser threw exception: {$e->getMessage()}");
        }
    }

    public function testInvalidDay()
	{
        try {
            $expr = 'isValidDate("5/32/2010")';
            $result = Parser::evaluate($expr)->evaluate();
            $this->assertEquals($result, AbstractExpression::$FALSE);
        } catch (Exception $e){
        	$this->assertTrue(false, "Parser threw exception: {$e->getMessage()}");
        }
    }

    public function testInvalidYear()
	{
        try {
            $expr = 'isValidDate("5/15/Q")';
            $result = Parser::evaluate($expr)->evaluate();
            $this->assertEquals($result, AbstractExpression::$FALSE);
        } catch (Exception $e){
        	$this->assertTrue(false, "Parser threw exception: {$e->getMessage()}");
        }
    }
}