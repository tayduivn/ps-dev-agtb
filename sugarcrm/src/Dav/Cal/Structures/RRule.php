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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Structures;

use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Property\ICalendar\Recur;

/**
 * Class RRule
 * @see     https://tools.ietf.org/html/rfc5545 RRULE section
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Structures
 */
class RRule
{
    /**
     * @var Recur
     */
    protected $rRule;

    /**
     * Map property name to validation class
     * @var array
     */
    protected static $validatorsMap = array(
        'FREQ' => 'Sugarcrm\\Sugarcrm\\Dav\\Cal\\Structures\\Validators\\Frequency',
        'BYMONTHDAY' => 'Sugarcrm\\Sugarcrm\\Dav\\Cal\\Structures\\Validators\\ByMonthDay',
        'BYYEARDAY' => 'Sugarcrm\\Sugarcrm\\Dav\\Cal\\Structures\\Validators\\ByYearDay',
        'BYWEEKNO' => 'Sugarcrm\\Sugarcrm\\Dav\\Cal\\Structures\\Validators\\ByWeekNo',
        'BYMONTH' => 'Sugarcrm\\Sugarcrm\\Dav\\Cal\\Structures\\Validators\\ByMonth',
        'INTERVAL' => 'Sugarcrm\\Sugarcrm\\Dav\\Cal\\Structures\\Validators\\Interval',
        'BYSETPOS' => 'Sugarcrm\\Sugarcrm\\Dav\\Cal\\Structures\\Validators\\BySetPos',
    );

    /**
     * RRule constructor.
     * @param Recur|null $rRule
     */
    public function __construct(Recur $rRule = null)
    {
        if ($rRule) {
            $this->rRule = $rRule;
        } else {
            $this->rRule = new Recur(new VCalendar(), 'RRULE');
        }
    }

    public function __clone()
    {
        $this->rRule = clone $this->rRule;
    }

    /**
     * Get validator object for parameter
     * @param $paramName
     * @return Validators\RRuleParam | null
     */
    protected function getValidator($paramName)
    {
        if (isset(static::$validatorsMap[$paramName])) {
            $validatorClass = \SugarAutoLoader::customClass(static::$validatorsMap[$paramName]);
            return new $validatorClass($this);
        }

        return null;
    }

    /**
     * Return array with $value
     * @param mixed $value
     * @return array | null
     */
    protected function toArray($value)
    {
        if (is_null($value)) {
            return null;
        }
        if (is_array($value)) {
            return $value;
        }

        return array($value);
    }

    /**
     * Get recurring rule parameter
     * @param string $name
     * @return mixed
     */
    protected function getParameter($name)
    {
        $params = $this->rRule->getParts();

        return isset($params[$name]) ? $params[$name] : null;
    }

    /**
     * Set recurring rule parameter
     * @param string $name
     * @param mixed $value
     * @return bool
     * @throws \InvalidArgumentException | \LogicException
     */
    protected function setParameter($name, $value)
    {
        if ((is_array($value) && !$value) || is_null($value)) {
            return $this->deleteParameter($name);
        }

        $validator = $this->getValidator($name);
        if ($validator) {
            $validator->validate($value);
        }

        $params = $this->rRule->getParts();

        $currentValue = $this->getParameter($name);

        if (!$currentValue || $currentValue != $value) {
            if (!is_array($value)) {
                $value = (string)$value;
            } elseif (count($value) == 1) {
                $value = (string)$value[0];
            }
            $params[$name] = $value;
            $this->rRule->setParts($params);

            return true;
        }

        return false;
    }

    /**
     * Delete recurring rule parameter
     * @param $name
     * @return bool
     */
    protected function deleteParameter($name)
    {
        $params = $this->rRule->getParts();

        if (isset($params[$name])) {
            unset($params[$name]);
            $this->rRule->setParts($params);

            return true;
        }

        return false;
    }

    /**
     * Get FREQ property of recurring
     * Available values "DAILY" / "WEEKLY" / "MONTHLY" / "YEARLY"
     * @return string
     */
    public function getFrequency()
    {
        return $this->getParameter('FREQ');
    }

    /**
     * Get recurring interval
     * @return int
     */
    public function getInterval()
    {
        return $this->getParameter('INTERVAL') ?: 1;
    }

    /**
     * Get count of repeating
     * @return int
     */
    public function getCount()
    {
        return $this->getParameter('COUNT');
    }

    /**
     * Get date when recurring will be ending
     * @return \SugarDateTime | null
     */
    public function getUntil()
    {
        $until = $this->getParameter('UNTIL');
        if ($until) {
            $untilDateTime = new \SugarDateTime($until, new \DateTimeZone('UTC'));
            return $this->normalizeUntil($untilDateTime);
        }

        return null;
    }

    /**
     * Get BYDAY property for WEEKLY recurring (Two letters MO, TU e t.c.)
     * @return array
     */
    public function getByDay()
    {
        return $this->toArray($this->getParameter('BYDAY'));
    }

    /**
     * Get BYMONTHDAY property of RRULE
     * @return array
     */
    public function getByMonthDay()
    {
        return $this->toArray($this->getParameter('BYMONTHDAY'));
    }

    /**
     * Get BYYEARDAY property of RRULE
     * @return array
     */
    public function getByYearDay()
    {
        return $this->toArray($this->getParameter('BYYEARDAY'));
    }

    /**
     * Get BYWEEKNO property of RRULE
     * @return array
     */
    public function getByWeekNo()
    {
        return $this->toArray($this->getParameter('BYWEEKNO'));
    }

    /**
     * Get BYMONTH property of RRULE
     * @return array
     */
    public function getByMonth()
    {
        return $this->toArray($this->getParameter('BYMONTH'));
    }

    /**
     * Get BYSETPOS property of RRULE
     * @return array
     */
    public function getBySetPos()
    {
        return $this->toArray($this->getParameter('BYSETPOS'));
    }

    /**
     * Set FREQ property of recurring
     * Available values "DAILY" / "WEEKLY" / "MONTHLY" / "YEARLY"
     * @param string $value
     * @return bool
     */
    public function setFrequency($value)
    {
        return $this->setParameter('FREQ', $value);
    }

    /**
     * Set recurring interval
     * @param int $value
     * @return bool
     */
    public function setInterval($value)
    {
        return $this->setParameter('INTERVAL', $value);
    }

    /**
     * Set count of repeating
     * @param int $value
     * @return bool
     */
    public function setCount($value)
    {
        $this->deleteParameter('UNTIL');

        return $this->setParameter('COUNT', $value);
    }

    /**
     * Set date when recurring will be ending
     * @param \SugarDateTime $value DateTime in UTC
     * @return bool
     */
    public function setUntil(\SugarDateTime $value)
    {
        if ($value != $this->getUntil()) {
            $value = $this->normalizeUntil($value);
            $value->setTimezone(new \DateTimeZone('UTC'));
            $until = $value->format('Ymd\THis\Z');

            $this->deleteParameter('COUNT');

            return $this->setParameter('UNTIL', $until);
        }

        return false;
    }

    /**
     * Set BYDAY property
     * @param array $value (Two letters MO, TU e t.c.)
     * @return bool
     */
    public function setByDay(array $value = null)
    {
        return $this->setParameter('BYDAY', $value);
    }

    /**
     * Set BYMONTHDAY property
     * @param array $values
     * @return bool
     * @throws \InvalidArgumentException | \LogicException
     */
    public function setByMonthDay(array $values = null)
    {
        return $this->setParameter('BYMONTHDAY', $values);
    }

    /**
     * Set BYYEARDAY property
     * @param array $values
     * @return bool
     * @throws \InvalidArgumentException | \LogicException
     */
    public function setByYearDay(array $values = null)
    {
        return $this->setParameter('BYYEARDAY', $values);
    }

    /**
     * Set BYWEEKNO property
     * @param array $values
     * @return bool
     * @throws \InvalidArgumentException | \LogicException
     */
    public function setByWeekNo(array $values = null)
    {
        return $this->setParameter('BYWEEKNO', $values);
    }

    /**
     * Set BYMONTH property
     * @param array $values
     * @return bool
     * @throws \InvalidArgumentException | \LogicException
     */
    public function setByMonth(array $values = null)
    {
        return $this->setParameter('BYMONTH', $values);
    }

    /**
     * Set BYSETPOS property
     * @param array $values
     * @return bool
     * @throws \InvalidArgumentException | \LogicException
     */
    public function setBySetPos(array $values = null)
    {
        return $this->setParameter('BYSETPOS', $values);
    }

    /**
     * Get Recurring object
     * @return Recur
     */
    public function getObject()
    {
        return $this->rRule;
    }

    /**
     * Set until date as day end datetime
     * @param \SugarDateTime $until
     * @return \SugarDateTime
     */
    public function normalizeUntil(\SugarDateTime $until)
    {
        return $until->get_day_end_time();
    }
}
