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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
require_once("include/Expressions/Expression/Boolean/BooleanExpression.php");

/**
 * <b>equal(Generic item1, Generic item2)</b><br>
 * Returns true if "item1" is equal to "item2".<br/>
 * ex: <i>equal("one", "one")</i> = true, <i>equal(1, "one")</i> = false
 */
class EqualExpression extends BooleanExpression
{
    /**
     * Returns itself when evaluating.
     */
    function evaluate()
    {
        $params = $this->getParameters();

        $a = $params[0]->evaluate();
        $b = $params[1]->evaluate();

        if ($a == $b) {
            return AbstractExpression::$TRUE;
        }
        return AbstractExpression::$FALSE;
    }

    /**
     * Returns the JS Equivalent of the evaluate function.
     */
    static function getJSEvaluate()
    {
        return <<<EOQ
            var SEE = SUGAR.expressions.Expression,
                params = this.getParameters(),
                a = params[0].evaluate(),
                b = params[1].evaluate(),
                hasBool = params[0] instanceof SUGAR.expressions.TrueExpression ||
                    params[1] instanceof SUGAR.expressions.TrueExpression;

            if ( a == b  || (hasBool && SEE.isTruthy(a) && SEE.isTruthy(b))) {
               return SEE.TRUE;
            }
            return SEE.FALSE;
EOQ;
    }

    /**
     * Any generic type will suffice.
     */
    static function getParameterTypes()
    {
        return array(AbstractExpression::$GENERIC_TYPE, AbstractExpression::$GENERIC_TYPE);
    }

    /**
     * Returns the maximum number of parameters needed.
     */
    static function getParamCount()
    {
        return 2;
    }

    /**
     * Returns the opreation name that this Expression should be
     * called by.
     */
    static function getOperationName()
    {
        return 'equal';
    }

    /**
     * Returns the String representation of this Expression.
     */
    function toString()
    {
    }
}
