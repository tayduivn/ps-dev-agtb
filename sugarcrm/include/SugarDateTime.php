<?php

class SugarDateTime extends DateTime
{
	protected $formats = array(
		"sec" => "s",
		"min" => "i",
		"hour" => "G",
		"zhour" => "H",
		"day" => "j",
		"zday" => "d",
		"days_in_month" => "t",
		"day_of_week" => "w",
		"day_of_year" => "z",
		"week" => "W",
		"month" => "n",
		"zmonth" => "m",
		"year" => "Y",
		"am_pm" => "A",
		"hour_12" => "g",
	);

	protected $var_gets = array(
		"24_hour" => "hour",
		"day_of_week" => "day_of_week_long",
		"day_of_week_short" => "day_of_week_short",
		"month_name" => "month_long",
		"hour" => "hour_12",
	);

	/**
	 * Copy of DateTime::createFromFormat
	 *
	 * Needed to return right type of the object
	 *
	 * @param string $format
	 * @param strinf $time
	 * @param DateTimeZone $timezone
	 * @return SugarDateTime
	 * @see DateTime::createFromFormat
	 */
	public static function createFromFormat($format, $time, DateTimeZone $timezone = null)
	{
		if(is_callable(array("DateTime", "createFromFormat"))) {
			// 5.3, hurray!
			if(!empty($timezone)) {
			    $d = parent::createFromFormat($format, $time, $timezone);
			} else {
			    $d = parent::createFromFormat($format, $time);
			}
		} else {
			// doh, 5.2, will have to simulate
			$d = self::_createFromFormat($format, $time, $timezone);
		}
		if(!$d) {
			return false;
		}
		$sd = new self();
		return $sd->setTimestamp($d->getTimestamp())->setTimezone($d->getTimezone());
	}

	protected static function _createFromFormat($format, $time, DateTimeZone $timezone)
	{
		$res = new self();
		$res->setTimezone($timezone);
		$str_format = str_replace(array_keys(TimeDate2::$format_to_str), array_values(TimeDate2::$format_to_str), $format);
		// TODO: better way to not risk locale stuff problems?
		$data = strptime($str_format, $time);
		$res->setDate($data["tm_year"], $data["tm_mon"], $data["tm_mday"])
			->setTime($data["tm_hour"], $data["tm_min"], $data["tm_sec"]);
		return $res;
	}

	/**
	 * Load language strings
	 * @param string $name string section to return
	 * @return array
	 */
	protected function _getStrings($name)
	{
		if(empty($this->_strings)) {
			$this->_strings = return_mod_list_strings_language($GLOBALS['current_language'],"Calendar");
		}
		return $this->_strings[$name];
	}

	/**
	 * Fetch property of the date by name
	 * @param string $var Property name
	 */
	public function __get($var)
	{
		// simple formats
		if(isset($this->formats[$var])) {
			return $this->format($this->formats[$var]);
		}
		// conditional, derived and translated ones
		switch($var) {
			case "ts":
				return $this->getTimestamp();
			case "tz_offset":
				return $this->getTimezone()->getOffset($this);
			case "days_in_year":
				return $this->format("L") == '1'?366:365;
				break;
			case "day_of_week_short":
				$str = $this->_getStrings('dom_cal_weekdays');
				return $str[$this->day_of_week];
			case "day_of_week_long":
				$str = $this->_getStrings('dom_cal_weekdays_long');
				return $str[$this->day_of_week];
			case "month_short":
				$str = $this->_getStrings('dom_cal_month');
				return $str[$this->month];
			case "month_long":
				$str = $this->_getStrings('dom_cal_month_long');
				return $str[$this->month];
		}

		return '';
	}

	/**
	 * Implement some get_ methods that fetch variables
	 *
	 * @param string $name
	 * @param array $args
	 */
	public function __call($name, $args)
	{
		// fill in 5.2.x gaps
		if($name == "getTimestamp") {
			return (int)$this->format('U');
		}
		if($name == "setTimestamp") {
			$sec = (int)$args[0];
			$sd = new self("@$sec");
			return $sd->setTimezone($this->getTimezone());
		}

		// getters
		if(substr($name, 0, 4) == "get_") {
			$var = substr($name, 4);

			if(isset($this->var_gets[$var])) {
				return $this->__get($this->var_gets[$var]);
			}

			if(isset($this->formats[$var])) {
				return $this->__get($var);
			}
		}
		sugar_die("SugarDateTime: unknowm method $name called");
		return false;
	}

	/**
	 * Get specific hour of today
	 * @param int $hour_index
	 * @return SugarDateTime
	 */
	public function get_datetime_by_index_today($hour_index)
	{
		if ( $hour_index < 0 || $hour_index > 23  )
		{
			sugar_die("hour is outside of range");
		}

		$newdate = clone $this;
		$newdate->setTime($hour_index, 0, 0);
		return $newdate;
	}

	function get_hour_end_time()
	{
		$newdate = clone $this;
		$newdate->setTime($this->hour, 59, 59);
		return $newdate;
	}

	function get_day_end_time()
	{
		$newdate = clone $this;
		$newdate->setTime(23, 59, 59);
		return $newdate;
	}

	function get_day_by_index_this_week($day_index)
	{
		$newdate = clone $this;
		$newdate->setDate($this->year, $this->month, $this->day +
			($day_index - $this->day_of_week));
		return $newdate;
	}

	function get_day_by_index_this_year($month_index)
	{
		$newdate = clone $this;
		$newdate->setDate($this->year, $month_index+1, 1);
		$newdate->modify("last day of this month");
		$newdate->setTime(0, 0);
		return $newdate;
	}

	function get_day_by_index_this_month($day_index)
	{
		$newdate = clone $this;
		$newdate->setDate($this->year, $this->month, $day_index+1);
		$newdate->setTime(0, 0);
		return $newdate;
	}

	/**
	 * Get new date, modified by date expression
	 *
	 * @example $yesterday = $today->get("yesterday");
	 *
	 * @param string $expression
	 * @return SugarDateTime
	 */
	function get($expression)
	{
		$newdate = clone $this;
		$newdate->modify($expression);
		return $newdate;
	}

	/**
	 * Display as DB date
	 * @return string
	 */
	function get_mysql_date()
	{
		return $this->format(TimeDate2::DB_DATE_FORMAT);
	}

	/**
	 * Create from ISO 8601 datetime
	 * @param string $str
	 * @return SugarDateTime
	 */
	static public function parse_utc_date_time($str)
	{
		return new self($str);
	}

	/**
	 * Create a list of time slots for calendar view
	 * Times must be in user TZ
	 * @param string $view Which view we are using - day, week, month
	 * @param SugarDateTime $start_time Start time
	 * @param SugarDateTime $end_time End time
	 */
	static function getHashList($view, $start_time, $end_time)
	{
		$hash_list = array();

  		if ( $view != 'day')
		{
		  $end_time = $end_time->get_day_end_time();
		}

		$end = $end_time->ts;
		if($end <= $start_time->ts) {
			$end = $start_time->ts+1;
		}

		$new_time = clone $start_time;
		$new_time->setTime($new_time->hour, 0, 0);

        while ($new_time->ts < $end) {
            if ($view == 'day') {
                $hash_list[] = $new_time->get_mysql_date() . ":" . $new_time->hour;
                $new_time->modify("next hour");
            } else {
                $hash_list[] = $new_time->get_mysql_date();
                $new_time->modify("next day");
            }
        }

		return $hash_list;
	}

	/**
	 * Get the beginning of the given day
	 */
	function get_day_begin($day = null, $month = null, $year = null)
	{
	    $newdate = clone $this;
	    $newdate->setDate(
	         $year?$year:$this->year,
	         $month?$month:$this->month,
	         $day?$day:$this->day);
	    $newdate->setTime(0, 0);
	    return $newdate;
	}

	/**
	 * Get the last timestamp of the given day
	 */
	function get_day_end($day = null, $month = null, $year = null)
	{
	    $newdate = clone $this;
	    $newdate->setDate(
	         $year?$year:$this->year,
	         $month?$month:$this->month,
	         $day?$day:$this->day);
	    $newdate->setTime(23, 59, 59);
	    return $newdate;
	}

	function get_year_begin($year)
	{
        $newdate = clone $this;
        $newdate->setDate($this->year, 1, 1);
        $newdate->setTime(0,0);
        return $newdate;
	}
	/*
	 * Print datetime in standard DB format
	 *
	 * Set $tz parameter to false if you are sure if the date is in UTC.
	 *
	 * @param bool $tz do conversion to UTC
	 * @return string
	 */
	function asDb($tz = true)
	{
        if($tz) {
            $this->setTimezone(new DateTimeZone("UTC"));
        }
        return $this->format(TimeDate2::DB_DATETIME_FORMAT);
	}

	/**
	 * Create datetime object from calendar array
	 * @param array $time
	 * @return SugarDateTime
	 */
	static function fromTimeArray($time)
	{
		if (! isset( $time) || count($time) == 0 )
		{
			$result = new self("now", new DateTimeZone("UTC"));
		}
		elseif ( isset( $time['ts']))
		{
			$result = new self("@".$time['ts'], new DateTimeZone("UTC"));
		}
		elseif ( isset( $time['date_str']))
		{
            $result = self::createFromFormat(TimeDate2::DB_DATE_FORMAT, $time['date_str']);
            $result->setTimezone(new DateTimeZone("UTC"));
		}
		else
		{
    		$hour = 0;
    		$min = 0;
    		$sec = 0;
    		$day = 1;
    		$month = 1;
    		$year = 1970;
		    if ( isset($time['sec']))
			{
        			$sec = $time['sec'];
			}
			if ( isset($time['min']))
			{
        			$min = $time['min'];
			}
			if ( isset($time['hour']))
			{
        			$hour = $time['hour'];
			}
			if ( isset($time['day']))
			{
        			$day = $time['day'];
			}
			if ( isset($time['month']))
			{
        			$month = $time['month'];
			}
			if ( isset($time['year']) && $time['year'] >= 1970)
			{
        			$year = $time['year'];
			}
			$result = $GLOBALS['timedate']->tzUser(new self("now"));
			$result->setDate($year, $month, $day)->setTime($hour, $min, $sec)->setTimeZone(new DateTimeZone("UTC"));
		}
        return $result;
	}

	/**
	 * Get query string for the date
	 * @return string
	 */
	function get_date_str()
	{
        return sprintf("&year=%d&month=%d&day=%d&hour=%d", $this->year, $this->month, $this->day, $this->hour);
	}
}
