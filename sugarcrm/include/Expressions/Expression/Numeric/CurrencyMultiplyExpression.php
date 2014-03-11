<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 */


require_once("include/Expressions/Expression/Numeric/NumericExpression.php");

/**
 * <b>currencyMultiply(Number n, ...)</b><br>
 * Multiplies the supplied numbers and returns the result as a string<br/>
 * ex: <i>currencyMultiply(-4, 2, 3)</i> = -24
 */
class CurrencyMultiplyExpression extends NumericExpression
{
    /**
     * Returns itself when evaluating.
     */
    public function evaluate()
    {
        // TODO: add caching of return values
        $product = 1;
        foreach ($this->getParameters() as $expr) {
            $product = SugarMath::init($product, 6)->mul($expr->evaluate())->result();
        }
        return (string)$product;
    }

    /**
     * Returns the JS Equivalent of the evaluate function.
     */
    public static function getJSEvaluate()
    {
        return <<<JS
			var params = this.getParameters(),
			product = 1;
			for (var i = 0; i < params.length; i++) {
                product = this.context.currencyMultiply(product, params[i].evaluate());
            }
			return product;
JS;
    }

    /**
     * Returns the opreation name that this Expression should be
     * called by.
     */
    public static function getOperationName()
    {
        return "currencyMultiply";
    }

    /**
     * Returns the String representation of this Expression.
     */
    public function toString()
    {
        $str = "";

        foreach ($this->getParameters() as $expr) {
            if (!$expr instanceof ConstantExpression) {
                $str .= "(";
            }
            $str .= $expr->toString() . " * ";
            if (!$expr instanceof ConstantExpression) {
                $str .= ")";
            }
        }
    }
}
