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
     * @param array $value
     * @param \SugarBean $bean
     *
     * @return \SugarBean $bean
     */
    public function arrayToBean(array $value, \SugarBean $bean)
    {
        if (isset($value['frequency'])) {
            $bean->repeat_type = $this->frequencyMap->getSugarValue($value['frequency'][0]);
        }

        if (isset($value['interval'])) {
            $bean->repeat_interval = $value['interval'][0];
        }

        if (isset($value['count'])) {
            $bean->repeat_count = $value['count'][0];
        }

        if (isset($value['until'])) {
            $bean->repeat_until = $value['until'][0];
        }

        if (isset($value['byday']) && $bean->repeat_type == 'Weekly') {
            $sugarValue = '';
            foreach ($value['byday'][0] as $day) {
                $sugarValue .= $this->dayMap->getSugarValue($day);
            }
            $bean->repeat_dow = $sugarValue;
        }

        if ($bean->repeat_type == 'Monthly') {
            $monthlySet = false;
            if (isset($value['byday'])) {
                $daysData = $value['byday'][0];
                $daysCount = count($daysData);
                switch ($daysCount) {
                    case 1:
                        $weekDay = substr($daysData[0], - 2);
                        $dayPosition = substr($daysData[0], 0, strlen($daysData[0]) - 2);
                        $bean->repeat_ordinal = $this->dayPositionMap->getSugarValue($dayPosition);
                        $bean->repeat_unit = $this->monthlyDayMap->getSugarValue($weekDay);
                        $bean->repeat_selector = 'On';
                        break;
                    case 2:
                        $bean->repeat_unit = 'WE';
                        $bean->repeat_selector = 'On';
                        break;
                    case 5:
                        $bean->repeat_unit = 'WD';
                        $bean->repeat_selector = 'On';
                        break;
                    case 7:
                        $bean->repeat_unit = 'Day';
                        $bean->repeat_selector = 'On';
                        break;
                    default:
                        $bean->repeat_selector = 'None';
                        break;
                }
                $monthlySet = true;
            }
            if (!empty($value['bysetpos'][0])) {
                $bean->repeat_ordinal = $this->dayPositionMap->getSugarValue($value['bysetpos'][0][0]);
                $monthlySet = true;
            }

            if (isset($value['bymonthday']) && $value['bymonthday'][0]) {
                $sDays = implode(',', $value['bymonthday'][0]);
                $bean->repeat_days = $sDays;
                $bean->repeat_selector = 'Each';
                $bean->repeat_ordinal = null;
                $monthlySet = true;
            }

            if (!$monthlySet) {
                $bean->repeat_selector = 'None';
            }
        } else {
            $bean->repeat_selector = 'None';
        }

        return $bean;
    }
}
