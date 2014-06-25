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
