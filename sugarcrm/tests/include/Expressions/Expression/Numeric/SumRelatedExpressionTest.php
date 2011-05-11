<?php
//FILE SUGARCRM flav=een ONLY
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
require_once("include/Expressions/Expression/Numeric/SumRelatedExpression.php");
require_once("include/Expressions/Expression/Parser/Parser.php");

/**
 * @outputBuffering enabled
 */   
class SumRelatedExpressionTest extends Sugar_PHPUnit_Framework_TestCase
{
    static $createdBeans = array();

	public static function setUpBeforeClass()
	{
	    $beanList = array();
	    $beanFiles = array();
	    require('include/modules.php');
	    $GLOBALS['beanList'] = $beanList;
	    $GLOBALS['beanFiles'] = $beanFiles;
	    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
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

    /**
     * @group bug39037
     */
	public function testRelatedSum()
	{
        $account = new Account();
            $account->name = "Sum Related Test";
            self::$createdBeans[] = $account;
            $account->save();

            $opp1 = new Opportunity();
            $opp1->name = "Sum Related Test Opp 1";
            $opp1->amount = $opp1->amount_usdollar = 100;
            $opp1->account_id = $account->id;
            $opp1->account_name = $account->name;

            self::$createdBeans[] = $opp1;
            $opp1->save();


            $opp2 = new Opportunity();
            $opp2->name = "Sum Related Test Opp 2";
            $opp2->amount = $opp1->amount_usdollar = 200;
            $opp2->account_id = $account->id;
            $opp2->account_name = $account->name;

            self::$createdBeans[] = $opp2;
            $opp2->save();
        try {
            $expr = 'rollupSum($opportunities, "amount")';
            $result = Parser::evaluate($expr, $account)->evaluate();
            $this->assertEquals($result, 300);
        } catch (Exception $e){
        	$this->assertTrue(false, "Parser threw exception: {$e->getMessage()}");
        }
    }
}