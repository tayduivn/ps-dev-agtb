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
 * <b>currencyAdd(Number n, ...)</b><br>
 * Returns the sum of the given currency numbers as a string.<br/>
 * ex: <i>currencyAdd(2, 1, 3)</i> = 6
 */
class CurrencyAddExpression extends NumericExpression
{
    /**
     * Returns itself when evaluating.
     */
    public function evaluate()
    {
        // TODO: add caching of return values
        $sum = 0;
        foreach ($this->getParameters() as $expr) {
            $sum = SugarMath::init($sum, 6)->add($expr->evaluate())->result();
        }
        return (string)$sum;
    }

    /**
     * Returns the JS Equivalent of the evaluate function.
     */
    public static function getJSEvaluate()
    {
        return <<<JS
			var params = this.getParameters(),
			sum = 0;
			for (var i = 0; i < params.length; i++) {
                sum = this.context.currencyAdd(sum, params[i].evaluate());
            }
			return sum;
JS;
    }

    /**
     * Returns the opreation name that this Expression should be
     * called by.
     */
    public static function getOperationName()
    {
        return "currencyAdd";
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
            $str .= $expr->toString();
            if (!$expr instanceof ConstantExpression) {
                $str .= ")";
            }
        }
    }
}
