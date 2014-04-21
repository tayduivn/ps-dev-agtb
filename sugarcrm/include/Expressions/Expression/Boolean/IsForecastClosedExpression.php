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

require_once("include/Expressions/Expression/Boolean/BooleanExpression.php");
/**
 * <b>isForecastClosed(String $status)</b><br>
 * Returns true if $status is in the forecast config for sales_stage_won or sales_stage_closed
 */
class isForecastClosedExpression extends BooleanExpression
{
    /**
     * Returns itself when evaluating.
     */
    public function evaluate()
    {
        $value = $this->getParameters()->evaluate();

        // get the statuses
        $settings = Forecast::getSettings();

        $valid_options = array_merge($settings['sales_stage_won'], $settings['sales_stage_lost']);

        if (in_array($value, $valid_options, true)) {
            return AbstractExpression::$TRUE;
        }

        return AbstractExpression::$FALSE;
    }

    /**
     * Returns the JS Equivalent of the evaluate function.
     */
    public static function getJSEvaluate()
    {
        return <<<JS
			var value = this.getParameters().evaluate();

			// this doesn't support BWC modules, so it should return false if it doesn't have app.
			// we can't use undersore as it's not in BWC mode here
			if (App === undefined) {
		        return SUGAR.expressions.Expression.FALSE;
			}

			var config = App.metadata.getModule('Forecasts', 'config'),
			    status = _.union(
                    config.sales_stage_won,
                    config.sales_stage_lost
                );

            if (status.indexOf(value) === -1) {
                return SUGAR.expressions.Expression.FALSE
            }

			return SUGAR.expressions.Expression.TRUE;
JS;
    }

    /**
     * Any generic type will suffice.
     */
    public static function getParameterTypes()
    {
        return array('string');
    }

    /**
     * Returns the maximum number of parameters needed.
     */
    public static function getParamCount()
    {
        return 1;
    }

    /**
     * Returns the operation name that this Expression should be
     * called by.
     */
    public static function getOperationName()
    {
        return 'isForecastClosed';
    }

    /**
     * Returns the String representation of this Expression.
     */
    public function toString()
    {
    }
}
