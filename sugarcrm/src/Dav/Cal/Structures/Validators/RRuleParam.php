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

use Sugarcrm\Sugarcrm\Dav\Cal\Structures;

/**
 * RRULE params validation
 * Class RRuleParam
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Structures\Validators
 */
class RRuleParam
{
    /**
     * @var Structures\RRule
     */
    protected $rRule;

    /**
     * Paraveter name
     * @var string
     */
    protected $paramName;

    /**
     * Map of forbidden "BYXXX" rules depending by frequency
     * @var array
     */
    protected $forbiddenFrequencyMap = array();

    /**
     * List of intervals for validation
     * @var array
     */
    protected $intervals = array();

    /**
     * RRuleParam constructor.
     * @param Structures\RRule $rRule
     */
    public function __construct(Structures\RRule $rRule)
    {
        $this->rRule = $rRule;
    }

    /**
     * Checking that RRULE parameter is suitable for current frequency
     * @throws \LogicException
     */
    protected function checkByFrequency()
    {
        $frequency = $this->rRule->getFrequency();

        if (!$frequency) {
            throw new \LogicException('FREQUENCY MUST be set at first. See https://tools.ietf.org/html/rfc5545');
        }

        if (in_array($frequency, $this->forbiddenFrequencyMap)) {
            throw new \LogicException('Can not set ' . $this->paramName .
                ' for current FREQUENCY=' . $frequency . '. See https://tools.ietf.org/html/rfc5545');
        }
    }

    /**
     * Check values by interval
     * @param mixed $values
     * @throws \InvalidArgumentException
     */
    protected function checkByInterval($values = null)
    {
        if (!is_null($values) && $this->intervals) {

            if (!is_array($values)) {
                $values = array($values);
            }
            foreach ($values as $value) {
                $inInterval = false;
                foreach ($this->intervals as $interval) {
                    $inInterval |= $value >= $interval['min'] && $value <= $interval['max'];
                }
                if (!$inInterval) {
                    throw new \InvalidArgumentException('Not supported value = ' . $value . ' for ' .
                        $this->paramName . '. See https://tools.ietf.org/html/rfc5545');
                }
            }
        }
    }

    /**
     * Makes validation and raise exception if errors found
     * @param mixed $value
     * @throws \InvalidArgumentException | \LogicException
     */
    public function validate($value)
    {
        if (!is_null($value)) {
            $this->checkByFrequency();
            $this->checkByInterval($value);
        }
    }
}
