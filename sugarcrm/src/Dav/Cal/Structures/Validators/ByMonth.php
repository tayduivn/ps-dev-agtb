<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\Sugarcrm\Dav\Cal\Structures\Validators;

/**
 * BYMONTH RRULE param validation
 * Class ByMonth
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Structures\Validators
 */
class ByMonth extends RRuleParam
{
    protected $paramName = 'BYMONTH';
    protected $intervals = array(
        array('min' => 1, 'max' => 12),
    );
}
