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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Structures;

use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Property\ICalendar\Recur;

class RRule
{
    protected $rRule;

    public function __construct(Recur $rRule = null)
    {
        if ($rRule) {
            $this->rRule = $rRule;
        } else {
            $this->rRule = new Recur(new VCalendar(), 'RRULE');
        }
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
     */
    protected function setParameter($name, $value)
    {
        if (!$value) {
            return false;
        }
        $params = $this->rRule->getParts();

        $currentValue = $this->getParameter($name);

        if (!$currentValue || $currentValue != $value) {
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
            return new \SugarDateTime($until, new \DateTimeZone('UTC'));
        }

        return null;
    }

    /**
     * Get BYDAY property for WEEKLY recurring (Two letters MO, TU e t.c.)
     * @return array
     */
    public function getByDay()
    {
        $byDay = $this->getParameter('BYDAY');

        if (!$byDay) {
            return array();
        }

        if (!is_array($byDay)) {
            return array($byDay);
        }

        return $byDay;
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
        $value = (string)$value;

        return $this->setParameter('INTERVAL', $value);
    }

    /**
     * Set count of repeating
     * @param int $value
     * @return bool
     */
    public function setCount($value)
    {
        $value = (string)$value;

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
    public function setByDay(array $value)
    {
        return $this->setParameter('BYDAY', $value);
    }

    /**
     * Get Recurring object
     * @return Recur
     */
    public function getObject()
    {
        return $this->rRule;
    }
}
