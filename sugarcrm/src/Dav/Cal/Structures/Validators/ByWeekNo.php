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
 * BYWEEKNO RRULE param validation
 * Class ByWeekNo
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Structures\Validators
 */
class ByWeekNo extends RRuleParam
{
    protected $paramName = 'BYWEEKNO';
    protected $intervals = array(
        array('min' => - 53, 'max' => - 1),
        array('min' => 1, 'max' => 53),
    );
    protected $forbiddenFrequencyMap = array('SECONDLY', 'MINUTELY', 'HOURLY', 'DAILY', 'WEEKLY', 'MONTHLY');
}
