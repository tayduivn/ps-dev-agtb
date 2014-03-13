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
 * <b>currencySubtract(Number a, Number b)</b><br>
 * Returns <i>a</i> minus <i>b</i>.<br/>
 * ex: <i>currencySubtract(9, 2, 3)</i> = 4
 */
class CurrencySubtractExpression extends NumericExpression
{
    /**
     * Returns itself when evaluating.
     */
    public function evaluate()
    {
        // TODO: add caching of return values
        $params = $this->getParameters();
        $diff = $params[0]->evaluate();
        for ($i = 1; $i < sizeof($params); $i++) {
            $diff = SugarMath::init($diff, 6)->add($params[$i]->evaluate())->result();
        }

        return (string)$diff;
    }

    /**
     * Returns the JS Equivalent of the evaluate function.
     */
    public static function getJSEvaluate()
    {
        return <<<JS
			var params = this.getParameters(),
			diff   = params[0].evaluate();
			for (var i = 1; i < params.length; i++) {
                diff = this.context.currencySubtract(diff, params[i].evaluate());
            }
			return diff;
JS;
    }

    /**
     * Returns the opreation name that this Expression should be
     * called by.
     */
    public static function getOperationName()
    {
        return "currencySubtract";
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
            $str .= $expr->toString() . " - ";
            if (!$expr instanceof ConstantExpression) {
                $str .= ")";
            }
        }
    }
}
