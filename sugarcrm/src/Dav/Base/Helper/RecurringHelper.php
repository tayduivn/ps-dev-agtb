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
    );

    /**
     * @var StatusMapper\IntervalMap
     */
    protected $frequencyMap;

    /**
     * @var StatusMapper\DayMap
     */
    protected $dayMap;

    public function __construct()
    {
        $this->frequencyMap = new StatusMapper\IntervalMap();
        $this->dayMap = new StatusMapper\DayMap();
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
        if ($recurring['repeat_dow']) {
            $aDow = str_split($recurring['repeat_dow']);
            foreach ($aDow as $value) {
                $convertedDow[] = $this->dayMap->getCalDavValue($value);
            }
        }
        $rRule->setByDay($convertedDow);

        return $rRule;
    }
}
