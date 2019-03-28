<?php declare(strict_types=1);
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

/**
 * Class BusinessCenter
 */
class BusinessCenter extends Basic
{
    public $table_name = 'business_centers';
    public $module_name = 'BusinessCenters';
    public $module_dir = 'BusinessCenters';
    public $object_name = 'BusinessCenter';

    // Stored fields
    public $address_street;
    public $address_city;
    public $address_state;
    public $address_country;
    public $address_postalcode;
    public $timezone;
    public $date_entered;
    public $date_modified;
    public $modified_user_id;
    public $assigned_user_id;
    public $created_by;
    public $created_by_name;
    public $modified_by_name;
    public $team_name;
    public $team_id;

    public $is_open_sunday;
    public $sunday_open_hour;
    public $sunday_open_minutes;
    public $sunday_close_hour;
    public $sunday_close_minutes;
    public $is_open_monday;
    public $monday_open_hour;
    public $monday_open_minutes;
    public $monday_close_hour;
    public $monday_close_minutes;
    public $is_open_tuesday;
    public $tuesday_open_hour;
    public $tuesday_open_minutes;
    public $tuesday_close_hour;
    public $tuesday_close_minutes;
    public $is_open_wednesday;
    public $wednesday_open_hour;
    public $wednesday_open_minutes;
    public $wednesday_close_hour;
    public $wednesday_close_minutes;
    public $is_open_thursday;
    public $thursday_open_hour;
    public $thursday_open_minutes;
    public $thursday_close_hour;
    public $thursday_close_minutes;
    public $is_open_friday;
    public $friday_open_hour;
    public $friday_open_minutes;
    public $friday_close_hour;
    public $friday_close_minutes;
    public $is_open_saturday;
    public $saturday_open_hour;
    public $saturday_open_minutes;
    public $saturday_close_hour;
    public $saturday_close_minutes;

    // Pseudo fields filled in on retrieve
    public $sunday_open;
    public $sunday_close;
    public $monday_open;
    public $monday_close;
    public $tuesday_open;
    public $tuesday_close;
    public $wednesday_open;
    public $wednesday_close;
    public $thursday_open;
    public $thursday_close;
    public $friday_open;
    public $friday_close;
    public $saturday_open;
    public $saturday_close;

    protected $businessHours = [
        'open' => [
            'sunday' => '',
            'monday' => '',
            'tuesday' => '',
            'wednesday' => '',
            'thursday' => '',
            'friday' => '',
            'saturday' => '',
        ],
        'close' => [
            'sunday' => '',
            'monday' => '',
            'tuesday' => '',
            'wednesday' => '',
            'thursday' => '',
            'friday' => '',
            'saturday' => '',
        ],
    ];

    protected $dayMap = [
        'su' => 'sunday',
        'm' => 'monday',
        't' => 'tuesday',
        'w' => 'wednesday',
        'th' => 'thursday',
        'f' => 'friday',
        's' => 'saturday',
    ];

    public $importable = true;

    protected $timeBases = [
        'open' => [
            'hour' => '00',
            'minutes' => '00',
        ],
        'close' => [
            'hour' => '23',
            'minutes' => '59',
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }

    /**
     * Gets the time prop for a day. This will be either open or close for a day
     * of the week, so something like `wednesday_open` or `sunday_close`.
     *
     * This method expects that `$day` has already been normalized.
     *
     * @param string $day Day of the week, or shortcode
     * @param string $type `open` or `close`
     * @return string
     */
    protected function getTimeProp(string $day, string $type)
    {
        return sprintf('%s_%s', $day, $type);
    }

    /**
     * Sets the open and close times for a day
     * @param string $day Day name or shortcode for a day of the week
     */
    public function setDayDefaults($day)
    {
        if ($this->isOpen($day)) {
            foreach ($this->timeBases as $type => $data) {
                foreach ($data as $time => $value) {
                    $prop = sprintf('%s_%s_%s', $day, $type, $time);
                    if (!isset($this->$prop)) {
                        $this->$prop = $value;
                    }
                }

                $prop = $this->getTimeProp($day, $type);
                $this->$prop = $this->{$prop . '_hour'} . $this->{$prop . '_minutes'};
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function fill_in_additional_detail_fields()
    {
        foreach ($this->dayMap as $day) {
            $this->setDayDefaults($day);
        }
    }

    /**
     * Gets a normalized name of a day
     * @param string $day Either the string name of a day or a mapped shortcut
     * @return string
     */
    public function getNormalizedDay(string $day)
    {
        $day = strtolower($day);
        if (!in_array($day, $this->dayMap)) {
            if (!isset($this->dayMap[$day])) {
                return false;
            }

            $day = $this->dayMap[$day];
        }

        return $day;
    }

    /**
     * Checks if this business center is open on a given day of the week
     * @param string $day Either the string name of a day or a mapped shortcut
     * @return boolean
     */
    public function isOpen(string $day)
    {
        $field = 'is_open_' . $this->getNormalizedDay($day);
        return (bool) $this->$field;
    }

    /**
     * Gets the open time for a day for this business center
     * @param string $day Day of the week, or shortcode
     * @return string|null
     */
    public function getOpenTime(string $day)
    {
        return $this->getTimeForTypeOnDay($day, 'open');
    }

    /**
     * Gets the close time for a day for this business center
     * @param string $day Day of the week, or shortcode
     * @return string|null
     */
    public function getCloseTime(string $day)
    {
        return $this->getTimeForTypeOnDay($day, 'close');
    }

    /**
     * Gets the open or close time for a day for this business center
     * @param string $day Day of the week, or shortcode
     * @return string|null
     */
    public function getTimeForTypeOnDay(string $day, string $type)
    {
        $type = trim(strtolower($type));
        if ($type !== 'open' && $type !== 'close') {
            return null;
        }

        $day = $this->getNormalizedDay($day);
        $prop = $this->getTimeProp($day, $type);
        return $this->$prop;
    }

    /**
     * Gets the list of hours for use in the hours portion of a time
     * @return array
     */
    public function getHoursDropdown()
    {
        return $this->getTimeUnitDropdown('hours');
    }

    /**
     * Gets the list of minutes for use in the minutes portion of a time
     * @return array
     */
    public function getMinutesDropdown()
    {
        return $this->getTimeUnitDropdown('minutes');
    }

    /**
     * Gets a list of hours or minutes for use in time dropdowns, based on type
     * @param string $unit Type of unit to get: `minutes` or `hours`
     * @return array
     */
    public function getTimeUnitDropdown(string $unit)
    {
        if ($unit !== 'minutes' && $unit !== 'hours') {
            return [];
        }

        // Set the loop limit to either 23 for hours or 59 for minutes
        $cap = $unit === 'hours' ? 23 : 59;

        // Prepare the response
        $r = [];
        for ($i = 0; $i <= $cap; $i++) {
            // Turn the numbers into 2space padded string representations: 00-23
            $r[$i] = str_pad("$i", 2, '0', STR_PAD_LEFT);
        }

        // Return it
        return $r;
    }

    /**
     * Gets the number of hours on a day that a business center is open
     * @param string $day Day of the week, or shortcode
     * @return float
     */
    public function getHoursOpenForDay(string $day)
    {
        if (!$this->isOpen($day)) {
            return 0.00;
        }

        $day = $this->getNormalizedDay($day);

        // Hours are easiest, so start with those
        $hoursDiff = (int)$this->{$day . '_close_hour'} - (int)$this->{$day . '_open_hour'};

        // Handle close minutes
        $cm = (int)$this->{$day . '_close_minutes'};

        // If close is 59 then move the close to 0 and add an hour
        if ($cm === 59) {
            $cm = 0;
            $hoursDiff += 1;
        }

        $om = (int)$this->{$day . '_open_minutes'};

        // If the open minutes are greater than the close minutes, adjust the
        // hours and minutes to account for it
        if ($om > $cm) {
            // Reduce the hours by one
            $hoursDiff -= 1;

            // And add an hours worth of minutes
            $cm += 60;
        }

        // Get a 2 precision rounded float value for the diff
        return round($hoursDiff + (($cm - $om) / 60), 2);
    }
}
