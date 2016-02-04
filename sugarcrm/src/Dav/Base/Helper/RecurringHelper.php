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

namespace Sugarcrm\Sugarcrm\Dav\Base\Helper;

use Sabre\VObject\Recur\EventIterator;
use Sabre\VObject\Component as DavComponent;
use Sugarcrm\Sugarcrm\Dav\Base\Constants;
use Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status as StatusMapper;
use Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule;

/**
 * Provide methods to convert Dav recurring to array and set Dav recurring from array
 * Class RecurringHelper
 * @package Sugarcrm\Sugarcrm\Dav\Base\Helper
 */
class RecurringHelper
{
    /**
     * List of recurring bean fields
     * @var array
     */
    public static $recurringFieldList = array(
        'repeat_type',
        'repeat_interval',
        'repeat_count',
        'repeat_until',
        'repeat_dow',
        'repeat_selector',
        'repeat_days',
        'repeat_ordinal',
        'repeat_unit',
    );

    /**
     * List of RRULE recurring fields
     * @var array
     */
    public static $rruleFieldList = array(
        'rrule_action',
        'rrule_frequency',
        'rrule_interval',
        'rrule_count',
        'rrule_until',
        'rrule_byday',
        'rrule_bymonthday',
        'rrule_bysetpos',
    );

    /**
     * @var StatusMapper\IntervalMap
     */
    protected $frequencyMap;

    /**
     * @var StatusMapper\DayMap
     */
    protected $dayMap;

    /**
     * @var StatusMapper\MonthlyDayMap
     */
    protected $monthlyDayMap;

    /**
     * @var StatusMapper\DayPositionMap
     */
    protected $dayPositionMap;

    public function __construct()
    {
        $this->frequencyMap = new StatusMapper\IntervalMap();
        $this->dayMap = new StatusMapper\DayMap();
        $this->monthlyDayMap = new StatusMapper\MonthlyDayMap();
        $this->dayPositionMap = new StatusMapper\DayPositionMap();
    }

    /**
     * Convert sugar bean recurring fields to array
     * @param \SugarBean $bean
     * @return array
     */
    public function beanToArray(\SugarBean $bean)
    {
        $result = array();
        foreach (static::$recurringFieldList as $field) {
            $result[$field] = $bean->$field;
        }

        return $result;
    }

    /**
     * Convert array to RRule
     * @see $recurringFieldList for avaliable fields
     * @param array $recurring
     * @return RRule
     */
    public function arrayToRRule(array $recurring)
    {
        $rRule = new RRule();
        $rRule->setFrequency($this->frequencyMap->getCalDavValue($recurring['repeat_type']));
        $rRule->setInterval($recurring['repeat_interval']);
        if ($recurring['repeat_count']) {
            $rRule->setCount($recurring['repeat_count']);
        } elseif ($recurring['repeat_until']) {
            $rRule->setUntil(new \SugarDateTime($recurring['repeat_until'], new \DateTimeZone('UTC')));
        }

        $convertedDow = array();
        if ($recurring['repeat_type'] == 'Monthly') {
            if ($recurring['repeat_ordinal'] && $recurring['repeat_unit'] && $recurring['repeat_selector'] == 'On') {
                if (!in_array($recurring['repeat_unit'], array('Day', 'WD', 'WE'))) {
                    $convertedDow[] = $this->dayPositionMap->getCalDavValue($recurring['repeat_ordinal']) .
                        $this->monthlyDayMap->getCalDavValue($recurring['repeat_unit']);
                } else {
                    switch ($recurring['repeat_unit']) {
                        case 'Day':
                            $convertedDow = array('SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA');
                            break;
                        case 'WD':
                            $convertedDow = array('MO', 'TU', 'WE', 'TH', 'FR');
                            break;
                        case 'WE':
                            $convertedDow = array('SU', 'SA');
                            break;
                    }
                    $rRule->setBySetPos(array($this->dayPositionMap->getCalDavValue($recurring['repeat_ordinal'])));
                }
            } else {
                $rRule->setBySetPos(array());
                $convertedDow = array();
            }
        } else {
            if ($recurring['repeat_dow']) {
                $aDow = str_split($recurring['repeat_dow']);
                foreach ($aDow as $value) {
                    $convertedDow[] = $this->dayMap->getCalDavValue($value);
                }
            }
        }
        $rRule->setByDay($convertedDow);

        $aDays = array();
        if ($recurring['repeat_type'] == 'Monthly') {
            if ($recurring['repeat_selector'] == 'Each' && $recurring['repeat_days']) {
                $aDays = explode(',', $recurring['repeat_days']);
            }
        }

        $rRule->setByMonthDay($aDays);

        return $rRule;
    }

    /**
     * Set bean fields for RRUle
     * Return true if fields was changed
     * @param array $value
     * @param \SugarBean $bean
     *
     * @return bool
     */
    public function arrayToBean(array $value, \SugarBean $bean)
    {
        $dateTimeHelper = new DateTimeHelper();
        $isChanged = false;
        if (isset($value['rrule_frequency'])) {
            $valueToSet =
                is_null($value['rrule_frequency'][0]) ? '' :
                    $this->frequencyMap->getSugarValue($value['rrule_frequency'][0]);
            if ($bean->repeat_type != $valueToSet) {
                $bean->repeat_type = $valueToSet;
                $isChanged = true;
            }
        }

        if (isset($value['rrule_interval'])) {
            $valueToSet = is_null($value['rrule_interval'][0]) ? 0 : $value['rrule_interval'][0];
            if ($bean->repeat_interval != $valueToSet) {
                $bean->repeat_interval = $valueToSet;
                $isChanged = true;
            }
        }

        if (isset($value['rrule_count'])) {
            $valueToSet = is_null($value['rrule_count'][0]) ? 0 : $value['rrule_count'][0];
            if ($bean->repeat_count != $valueToSet) {
                $bean->repeat_count = $valueToSet;
                $isChanged = true;
            }
        }

        if (isset($value['rrule_until'])) {
            $valueToSet = is_null($value['rrule_until'][0]) ? '' : $value['rrule_until'][0];
            $beanValue = $bean->repeat_until ? $dateTimeHelper->sugarDateToUTC($bean->repeat_until)->asDbDate() : '';
            if ($beanValue != $valueToSet) {
                $bean->repeat_until = $valueToSet;
                $isChanged = true;
            }
        }

        if (empty($bean->repeat_count) && empty($bean->repeat_until)) {
            $bean->repeat_count = Constants::MAX_INFINITE_RECCURENCE_COUNT;
            $isChanged = true;
        }

        if (isset($value['rrule_byday']) && $bean->repeat_type == 'Weekly') {
            $sugarValue = '';
            if (!is_null($value['rrule_byday'][0])) {
                foreach ($value['rrule_byday'][0] as $day) {
                    $sugarValue .= $this->dayMap->getSugarValue($day);
                }
            }
            if ($bean->repeat_dow != $sugarValue) {
                $bean->repeat_dow = $sugarValue;
                $isChanged = true;
            }
        }

        if ($bean->repeat_type == 'Monthly') {
            if (isset($value['rrule_byday'])) {
                if (!is_null($value['rrule_byday'][0])) {
                    $daysData = $value['rrule_byday'][0];
                    $daysCount = count($daysData);
                } else {
                    $daysCount = 0;
                }
                switch ($daysCount) {
                    case 1:
                        $weekDay = substr($daysData[0], - 2);
                        $dayPosition = substr($daysData[0], 0, strlen($daysData[0]) - 2);
                        $davRepeatOrdinal = $this->dayPositionMap->getSugarValue($dayPosition);
                        $davRepeatUnit = $this->monthlyDayMap->getSugarValue($weekDay);
                        if ($bean->repeat_ordinal != $davRepeatOrdinal) {
                            $bean->repeat_ordinal = $davRepeatOrdinal;
                            $isChanged = true;
                        }
                        if ($bean->repeat_unit != $davRepeatUnit) {
                            $bean->repeat_unit = $davRepeatUnit;
                            $isChanged = true;
                        }
                        $bean->repeat_selector = 'On';
                        break;
                    case 2:
                        if ($bean->repeat_unit != 'WE') {
                            $bean->repeat_unit = 'WE';
                            $isChanged = true;
                        }
                        $bean->repeat_selector = 'On';
                        break;
                    case 5:
                        if ($bean->repeat_unit != 'WD') {
                            $bean->repeat_unit = 'WD';
                            $isChanged = true;
                        }
                        $bean->repeat_selector = 'On';
                        break;
                    case 7:
                        if ($bean->repeat_unit != 'Day') {
                            $bean->repeat_unit = 'Day';
                            $isChanged = true;
                        }
                        $bean->repeat_selector = 'On';
                        break;
                    default:
                        $bean->repeat_selector = 'None';
                        if (!empty($bean->repeat_ordinal)) {
                            $bean->repeat_ordinal = '';
                            $isChanged = true;
                        }
                        if (!empty($bean->repeat_unit)) {
                            $bean->repeat_unit = '';
                            $isChanged = true;
                        }
                        break;
                }
            }
            if (isset($value['rrule_bysetpos'])) {
                if (is_null($value['rrule_bysetpos'][0])) {
                    if (!empty($bean->repeat_ordinal)) {
                        $bean->repeat_ordinal = '';
                        $isChanged = true;
                    }
                } else {
                    $davRepeatOrdinal = $this->dayPositionMap->getSugarValue($value['rrule_bysetpos'][0][0]);
                    if ($bean->repeat_ordinal != $davRepeatOrdinal) {
                        $bean->repeat_ordinal = $davRepeatOrdinal;
                        $isChanged = true;
                    }
                }
            }

            if (isset($value['rrule_bymonthday'])) {
                $sDays = !is_null($value['rrule_bymonthday'][0]) ? implode(',', $value['rrule_bymonthday'][0]) : '';
                if ($sDays && $bean->repeat_days != $sDays) {
                    $bean->repeat_days = $sDays;
                    $isChanged = true;
                    if ($sDays) {
                        $bean->repeat_selector = 'Each';
                        $bean->repeat_ordinal = '';
                    }
                }
            }
        } else {
            if (!empty($this->repeat_unit)) {
                $this->repeat_unit = '';
                $isChanged = true;
            }
            if (!empty($this->repeat_ordinal)) {
                $this->repeat_ordinal = '';
                $isChanged = true;
            }
            if (!empty($this->repeat_days)) {
                $this->repeat_days = '';
                $isChanged = true;
            }

            $bean->repeat_selector = 'None';
        }

        return $isChanged;
    }
}
