<?php
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

require_once("include/Expressions/Expression/Numeric/CurrencySumRelatedExpression.php");
require_once("include/Expressions/Expression/Parser/Parser.php");

class CurrencySumRelatedExpressionTest extends Sugar_PHPUnit_Framework_TestCase
{
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
        $result = SugarMath::init($result, 2)->result();
        $this->assertEquals('1611.11', $result);

    }


    /**
     * @ticket BR-437
     * @group expressions
     */
    public function testRelatedCurrencySumWithNonBaseOppCurrency()
    {
        $currency = SugarTestCurrencyUtilities::createCurrency('Eur','€','EUR', 0.9);
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->currency_id = $currency->id;
        $opp->base_rate = 0.9;

        $product1 = SugarTestProductUtilities::createProduct();
        $product1->likely_case = '1000.00';
        $product1->opportunity_id = $opp->id;
        $product1->save();

        $expr = 'rollupCurrencySum($products, "likely_case")';
        $result = Parser::evaluate($expr, $opp)->evaluate();
        $result = SugarMath::init($result, 2)->result();
        $this->assertEquals('900.00', $result);

    }
}
