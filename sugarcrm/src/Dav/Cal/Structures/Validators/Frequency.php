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
 * FREQ RRULE param validation
 * Class BySeconds
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Structures\Validators
 */
class Frequency extends RRuleParam
{
    protected $paramName = 'FREQ';

    /**
     * List of allowed frequency
     * @var array
     */
    protected static $allowedFrequency = array(
        'SECONDLY',
        'MINUTELY',
        'HOURLY',
        'DAILY',
        'WEEKLY',
        'MONTHLY',
        'YEARLY',
    );

    /**
     * @inheritdoc
     */
    public function validate($value)
    {
        if (!in_array($value, static::$allowedFrequency)) {
            throw new \InvalidArgumentException('Not supported value for FREQ=' . $value);
        }
    }
}
