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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Structures\Validators;

/**
 * INTERVAL RRULE param validation
 * Class BySeconds
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Structures\Validators
 */
class Interval extends RRuleParam
{
    protected $paramName = 'INTERVAL';
    protected $intervals = array(
        array('min' => 1, 'max' => PHP_INT_MAX),
    );
}
