<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

require_once("include/Expressions/Expression/Numeric/CurrencySumRelatedExpression.php");
require_once("include/Expressions/Expression/Parser/Parser.php");

class CurrencySumRelatedExpressionTest extends Sugar_PHPUnit_Framework_TestCase
{
    
    public function setUp()
    {
            $this->markTestIncomplete("SFA - This is failing in strict mode because of a bad date format, usually all 0's");
    }    
	public static function setUpBeforeClass()
	{
	    parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
	}

	public function tearDown()
	{
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
	}


    /**
     * @ticket SFA-745
     * @group expressions
     */
	public function testRelatedCurrencySumWithNonBaseCurrency()
	{
        $currency = SugarTestCurrencyUtilities::createCurrency('Eur','€','EUR', 0.9);
        $opp = SugarTestOpportunityUtilities::createOpportunity();

        $product1 = SugarTestProductUtilities::createProduct();
        $product1->likely_case = '1000.00';
        $product1->currency_id = $currency->id;
        $product1->opportunity_id = $opp->id;
        $product1->save();

        $product2 = SugarTestProductUtilities::createProduct();
        $product2->likely_case = '500.00';
        $product2->opportunity_id = $opp->id;
        $product2->save();

        $expr = 'rollupCurrencySum($products, "likely_case")';
        $result = Parser::evaluate($expr, $opp)->evaluate();
        $this->assertEquals('1611.11', $result);

    }
}