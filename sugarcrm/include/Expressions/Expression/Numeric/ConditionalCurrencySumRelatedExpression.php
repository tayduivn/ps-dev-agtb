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
require_once('include/Expressions/Expression/Numeric/NumericExpression.php');

/**
 * <b>rollupConditionalCurrencySum(Relate <i>link</i>, String <i>field</i>, Field <i>string</i>, Values <i>list</i>)</b><br>
 * Returns the sum of the values of <i>field</i> in records related by <i>link</i><br/>
 * ex: <i>rollupConditionalCurrencySum($products, "likely_case", "discount_select", "1")</i> in Opportunities would return the <br/>
 * sum of the likely_case field converted to base currency for all the products related to this Opportunity
 */
class ConditionalCurrencySumRelatedExpression extends NumericExpression
{
    /**
     * Returns the entire enumeration bare.
     */
    public function evaluate()
    {
        $params = $this->getParameters();
        // This should be of relate type, which means an array of SugarBean objects
        $linkField = $params[0]->evaluate();
        $relfield = $params[1]->evaluate();

        $conditionalField = $params[2]->evaluate();
        $conditionalValues = $params[3]->evaluate();

        if (!is_array($conditionalValues)) {
            $conditionalValues = array($conditionalValues);
        }

        $ret = 0;

        if (!is_array($linkField) || empty($linkField)) {
            return $ret;
        }

        if (!isset($this->context)) {
            //If we don't have a context provided, we have to guess. This can be a large performance hit.
            $this->setContext();
        }
        $toRate = isset($this->context->base_rate) ? $this->context->base_rate : null;

        foreach ($linkField as $bean) {
            if (!in_array($bean->$conditionalField, $conditionalValues)) {
                continue;
            }
            if (!empty($bean->$relfield)) {
                $ret = SugarMath::init($ret)->add(
                    SugarCurrency::convertWithRate($bean->$relfield, $bean->base_rate, $toRate)
                )->result();
            }
        }

        return $ret;
    }

    /**
     * Returns the JS Equivalent of the evaluate function.
     */
    public static function getJSEvaluate()
    {
        return false;
    }

    /**
     * Returns the opreation name that this Expression should be
     * called by.
     */
    public static function getOperationName()
    {
        return array("rollupConditionalCurrencySum");
    }

    /**
     * The first parameter is a number and the second is the list.
     */
    public static function getParameterTypes()
    {
        return array(
            AbstractExpression::$RELATE_TYPE,
            AbstractExpression::$STRING_TYPE,
            AbstractExpression::$STRING_TYPE,
            AbstractExpression::$GENERIC_TYPE
        );
    }

    /**
     * Returns the maximum number of parameters needed.
     */
    public static function getParamCount()
    {
        return 4;
    }
}
