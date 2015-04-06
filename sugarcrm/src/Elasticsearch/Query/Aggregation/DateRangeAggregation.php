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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Query\Aggregation;

/**
 * Class DateRangeAggregation
 * Covers date range Today, Yesterday, Tomorrow, Last 7 days, Next 7 days, Last 30 Days, Next 30 Days,
 * Last Month, This month, Next Month, Last Year, Next Year, This Year.
 */
class DateRangeAggregation extends RangeAggregation
{
    const ELASTIC_DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * The list of pre-defined dates to be used for the aggregation
     * @var array
     */
    protected $dateNames = array(
        'today', 'yesterday', 'tomorrow', 'last_7_days', 'next_7_days', 'last_30_days', 'next_30_days',
        'last_month', 'this_month', 'next_month', 'last_year', 'this_year', 'next_year'
    );

    /**
     * Aggregation for date created constructor
     * @see AggregationRange::__construct()
     */
    public function __construct()
    {
        $defaultOpts = array(
            'ranges' => $this->initRanges(),
            'typeExt' => ".gs_datetime",
        );
        parent::__construct($defaultOpts);
    }

    /**
     * Creates range options for our Aggregation depending on current datetime.
     * @return array
     */
    protected function initRanges()
    {
        $ranges = array();
        foreach ($this->dateNames as $dateName) {
            $date = \TimeDate::getInstance()->parseDateRange($dateName);
            if (!empty($date)) {
                $from = $this->timestampToDate($date[0]->getTimestamp());
                $to =  $this->timestampToDate($date[1]->getTimestamp());

                // Here the date name is the id/key of the range
                $ranges[$dateName] = array(
                    'from' => $from,
                    'to' => $to,
                    'key' => $dateName
                );
            }
        }
        return $ranges;
    }


    /**
     * Convert timestamp to Datetime string
     * format: '2013-07-08 00:00:00'
     * @param $time
     * @return string
     */
    protected function timestampToDate($time)
    {
        return date(self::ELASTIC_DATETIME_FORMAT, $time);
    }
}
