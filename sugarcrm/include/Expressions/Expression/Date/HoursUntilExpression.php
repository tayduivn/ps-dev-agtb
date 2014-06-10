<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

require_once 'include/Expressions/Expression/Numeric/NumericExpression.php';

/**
 * <b>hoursUntil(Date d)</b><br>
 * Returns number of hours from now until the specified date.
 */
class HoursUntilExpression extends NumericExpression
{
    /**
     * Returns number of hours from now until the specified date.
     */
    public function evaluate()
    {
        $params = DateExpression::parse($this->getParameters()->evaluate());
        if (!$params) {
            return false;
        }

        $now = TimeDate::getInstance()->getNow();
        $tsdiff = $params->ts - $now->ts;

        return (int) ($tsdiff / 3600);
    }

    /**
     * Returns the JS Equivalent of the evaluate function.
     */
    public static function getJSEvaluate()
    {
        return <<<EOQ
            var then = SUGAR.util.DateUtils.parse(this.getParameters().evaluate());
            var now = new Date();
            var diff = then - now;

            return ~~(diff / 3600000);
EOQ;
    }

    /**
     * Returns the operation name that this Expression should be
     * called by.
     */
    public static function getOperationName()
    {
        return 'hoursUntil';
    }

    /**
     * All parameters have to be a date.
     */
    public static function getParameterTypes()
    {
        return array(AbstractExpression::$DATE_TYPE);
    }

    /**
     * Returns the maximum number of parameters needed.
     */
    public static function getParamCount()
    {
        return 1;
    }

    /**
     * Returns the String representation of this Expression.
     */
    public function toString()
    {
    }
}
