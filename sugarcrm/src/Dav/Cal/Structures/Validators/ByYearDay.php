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
 * BYYEARDAY RRULE param validation
 * Class ByYearDay
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Structures\Validators
 */
class ByYearDay extends RRuleParam
{
    protected $paramName = 'BYYEARDAY';
    protected $intervals = array(
        array('min' => -366, 'max' => -1),
        array('min' => 1, 'max' => 366),
    );
    protected $forbiddenFrequencyMap = array('DAILY', 'WEEKLY', 'MONTHLY');
}
