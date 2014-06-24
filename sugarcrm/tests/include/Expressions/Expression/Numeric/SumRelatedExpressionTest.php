<?php
//FILE SUGARCRM flav=een ONLY
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
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