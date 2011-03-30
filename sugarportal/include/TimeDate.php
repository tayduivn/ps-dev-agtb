<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once 'include/SugarDateTime.php';

/**
  *
  * New Time & Date handling class
  * Migration notes:
  * - to_db_time() requires either full datetime or time, won't work with just date
  * 	The reason is that it's not possible to know if short string has only date or only time,
  *     and it makes more sense to assume time for the time conversion function.
  */
class TimeDate
{
	const DB_DATE_FORMAT = 'Y-m-d';
	const DB_TIME_FORMAT = 'H:i:s';
    // little optimization
	const DB_DATETIME_FORMAT = 'Y-m-d H:i:s';
	const RFC2616_FORMAT = 'D, d M Y H:i:s \G\M\T';

    // Standard DB date/time formats
    // they are constant, vars are for compatibility
	public $dbDayFormat = self::DB_DATE_FORMAT;
    public $dbTimeFormat = self::DB_TIME_FORMAT;

    /**
     * Regexp for matching format elements
     * @var array
     */
    protected static $format_to_regexp = array(
    	'a' => '[ap]m',
    	'A' => '[AP]M',
    	'd' => '[0-9]{1,2}',
    	'j' => '[0-9]{1,2}',
    	'h' => '[0-9]{1,2}',
    	'H' => '[0-9]{1,2}',
    	'g' => '[0-9]{1,2}',
    	'G' => '[0-9]{1,2}',
   		'i' => '[0-9]{1,2}',
    	'm' => '[0-9]{1,2}',
    	'n' => '[0-9]{1,2}',
    	'Y' => '[0-9]{4}',
        's' => '[0-9]{1,2}',
    	'F' => '\w+',
    	"M" => '[\w]{1,3}',
    );

    /**
     * Relation between date() and strftime() formats
     * @var array
     */
    public static $format_to_str = array(
		// date
    	'Y' => '%Y',

    	'm' => '%m',
    	'M' => '%b',
    	'F' => '%B',
	    'n' => '%m',

       	'd' => '%d',
    	//'j' => '%e',
    	// time
       	'a' => '%P',
       	'A' => '%p',

    	'h' => '%I',
       	'H' => '%H',
    	//'g' => '%l',
       	//'G' => '%H',

       	'i' => '%M',
       	's' => '%S',
    );

    /**
     * GMT timezone object
     *
     * @var DateTimeZone
     */
    protected static $gmtTimezone;

    /**
     * Current time
     * @var SugarDateTime
     */
    protected $now;

    /**
     * The current user
     *
     * @var User
     */
    protected $user;

    /**
     * Current user's ID
     *
     * @var string
     */
    protected $current_user_id;
    /**
     * Current user's TZ
     * @var DateTimeZone
     */
    protected $current_user_tz;

    /**
     * Separator for current user time format
     *
     * @var string
     */
    protected $time_separator;

    /**
     * Always consider user TZ to be GMT - for SOAP etc.
     *
     * @var bool
     */
    protected $always_gmt = false;

    /**
     * Global instance of TimeDate
     * @var TimeDate
     */
    protected static $timedate;

    public $allow_cache = true;

    public function __construct(User $user = null)
    {
        if (self::$gmtTimezone == null) {
            self::$gmtTimezone = new DateTimeZone("UTC");
        }
        $this->now = new SugarDateTime();
        $this->tzGMT($this->now);
        $this->user = $user;
    }

    /**
     * Get TimeDate instance
     * @return TimeDate
     */
    public static function getInstance()
    {
        if(empty(self::$timedate)) {
            self::$timedate = new self;
        }
        return self::$timedate;
    }

    /**
     * Set current user for this object
     *
     * @param $user
     * @return TimeDate
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
        $this->clearCache();
        return $this;
    }

    /**
     * Set always using GMT zone
     * @param bool $gmt
     */
    public function setAlwaysGmt($gmt = true)
    {
    	$this->always_gmt = $gmt;
    	return $this;
    }

     /**
     * Figure out what the required user is
     *
     * The order is: supplied parameter, TimeDate's user, global current user
     *
     * @param User $user
     * @return User
     */
    protected function _getUser(User $user = null)
    {
        if (empty($user)) {
            $user = $this->user;
        }
        if (empty($user)) {
            $user = $GLOBALS['current_user'];
        }
        return $user;
    }

    /**
     * Get timezone for the specified user
     *
     * @param User $user
     * @return DateTimeZone
     */
    protected function _getUserTZ(User $user = null)
    {
        $user = $this->_getUser($user);

        if (empty($user) || $this->always_gmt) {
            return self::$gmtTimezone;
        }

        if ($this->allow_cache && $user->id == $this->current_user_id && ! empty($this->current_user_tz)) {
            // current user is cached
            return $this->current_user_tz;
        }

        try {
            $usertimezone = $user->getPreference('timezone');
            if(empty($usertimezone)) {
                return self::$gmtTimezone;
            }
            $tz = new DateTimeZone($usertimezone);
        } catch (Exception $e) {
            $GLOBALS['log']->fatal('Unknown timezone: ' . $usertimezone);
            return self::$gmtTimezone;
        }

        if (empty($this->current_user_id)) {
            $this->current_user_id = $user->id;
            $this->current_user_tz = $tz;
        }

        return $tz;
    }

    /**
     * Clears all cached data regarding current user
     */
    public function clearCache()
    {
        $this->current_user_id = null;
        $this->current_user_tz = null;
        $this->time_separator = null;
        $this->now = new SugarDateTime();
    }

    /**
     * Get user date format.
     * @todo add caching
     *
     * @param [User] $user user object, current user if not specified
     * @return string
     */
    public function get_date_format(User $user = null)
    {

        $user = $this->_getUser($user);

        if (empty($user)) {
            return '';
        }

        $datef = $user->getPreference('datef');
        if(empty($datef) && isset($GLOBALS['current_user']) && $GLOBALS['current_user'] !== $user) {
            // if we got another user and it has no date format, try current user
            $datef = $GLOBALS['current_user']->getPreference('datef');
        }
        if (empty($datef)) {
            $datef = $GLOBALS['sugar_config']['default_date_format'];
        }
        if (empty($datef)) {
            $datef = '';
        }

        return $datef;
    }

    /**
     * Get user time format.
     * @todo add caching
     *
     * @param [User] $user user object, current user if not specified
     * @return string
     */
    public function get_time_format(User $user = null)
    {
        $user = $this->_getUser($user);

        if (empty($user)) {
            return '';
        }

        $timef = $user->getPreference('timef');
        if(empty($timef) && isset($GLOBALS['current_user']) && $GLOBALS['current_user'] !== $user) {
            // if we got another user and it has no time format, try current user
            $timef = $GLOBALS['current_user']->getPreference('$timef');
        }
        if (empty($timef)) {
            $timef = $GLOBALS['sugar_config']['default_time_format'];
        }
        if (empty($timef)) {
            $timef = '';
        }
        return $timef;
    }

    /**
     * Get user datetime format.
     * @todo add caching
     *
     * @param [User] $user user object, current user if not specified
     * @return string
     */
    public function get_date_time_format(User $user = null)
    {
        return $this->merge_date_time($this->get_date_format($user), $this->get_time_format($user));
    }

    /**
     * Make one datetime string from date string and time string
     *
     * @param string $date
     * @param string $time
     * @return string New datetime string
     */
    function merge_date_time($date, $time)
    {
        return $date . ' ' . $time;
    }

    /**
     * Split datetime string into date & time
     *
     * @param string $datetime
     * @return array
     */
    function split_date_time($datetime)
    {
        return explode(' ', $datetime);
    }

    function get_cal_date_format()
    {
        return str_replace(array_keys(self::$format_to_str), array_values(self::$format_to_str), $this->get_date_format());
    }

    function get_cal_time_format()
    {
        return str_replace(array_keys(self::$format_to_str), array_values(self::$format_to_str), $this->get_time_format());
    }

    function get_cal_date_time_format()
    {
        return str_replace(array_keys(self::$format_to_str), array_values(self::$format_to_str), $this->get_date_time_format());
    }

    /**
     * Verify if the date string conforms to a format
     *
     * @param string $date
     * @param string $format Format to check
     * @param string $toformat
     * @return bool Is the date ok?
     */
    public function check_matching_format($date, $format)
    {
        try {
            $dt = SugarDateTime::createFromFormat($format, $date);
            if (!is_object($dt)) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Format DateTime object as DB datetime
     *
     * @param DateTime $date
     * @return string
     */
    public function asDb(DateTime $date)
    {
        $date->setTimezone(self::$gmtTimezone);
        return $date->format($this->get_db_date_time_format());
    }

    /**
     * Format DateTime object as user datetime
     *
     * @param DateTime $date
     * @return string
     */
    public function asUser(DateTime $date, User $user = null)
    {
        $this->tzUser($date, $user);
        return $date->format($this->get_date_time_format($user));
    }

    /**
     * Produce timestamp offset by user's timezone
     *
     * So if somebody converts it to format assuming GMT, it would actually display user's time.
     * This is used by Javascript.
     *
     * @param DateTime $date
     * @return int
     */
    public function asUserTs(DateTime $date)
    {
        return $date->format('U')+$this->_getUserTZ()->getOffset($date);
    }

    /**
     * Format DateTime object as DB date
     * Note: by default does not convert TZ!
     * @param DateTime $date
     * @param boolean $tz Perform TZ conversion?
     * @return string
     */
    public function asDbDate(DateTime $date, $tz = false)
    {
        if($tz) $date->setTimezone(self::$gmtTimezone);
        return $date->format($this->get_db_date_format());
    }

    /**
     * Format DateTime object as user date
     * Note: by default does not convert TZ!
     * @param DateTime $date
     * @param boolean $tz Perform TZ conversion?
     * @return string
     */
    public function asUserDate(DateTime $date, $tz = false)
    {
        if($tz) $this->tzUser($date);
        return $date->format($this->get_date_format());
    }

    /**
     * Format DateTime object as DB time
     *
     * @param DateTime $date
     * @return string
     */
    public function asDbTime(DateTime $date)
    {
        $date->setTimezone(self::$gmtTimezone);
        return $date->format($this->get_db_time_format());
    }

    /**
     * Format DateTime object as user time
     *
     * @param DateTime $date
     * @return string
     */
    public function asUserTime(DateTime $date)
    {
        $this->tzUser($date);
        return $date->format($this->get_time_format());
    }

    /**
     * Get DateTime from DB datetime string
     *
     * @param string $date
     * @return SugarDateTime
     */
    public function fromDb($date)
    {
        try {
            return SugarDateTime::createFromFormat($this->get_db_date_time_format(), $date, self::$gmtTimezone);
        } catch (Exception $e) {
            $GLOBALS['log']->error("fromDb: Conversion of $date from DB format failed: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Get DateTime from DB datetime string using non-standard format
     *
     * Non-standard format usually would be only date, only time, etc.
     *
     * @param string $date
     * @param string $format format to accept
     * @return SugarDateTime
     */
    public function fromDbFormat($date, $format)
    {
        try {
            return SugarDateTime::createFromFormat($format, $date, self::$gmtTimezone);
        } catch (Exception $e) {
            $GLOBALS['log']->error("fromDbFormat: Conversion of $date from DB format $format failed: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Get DateTime from user datetime string
     *
     * @param string $date
     * @return SugarDateTime
     */
    public function fromUser($date, User $user = null)
    {
        try {
            return SugarDateTime::createFromFormat($this->get_date_time_format($user), $date, $this->_getUserTZ($user));
        } catch (Exception $e) {
            $uf = $this->get_date_time_format($user);
            $GLOBALS['log']->error("fromUser: Conversion of $date from user format $uf failed: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Get DateTime from user time string
     *
     * @param string $date
     * @return SugarDateTime
     */
    public function fromUserTime($date, User $user = null)
    {
        try {
            return SugarDateTime::createFromFormat($this->get_time_format($user), $date, $this->_getUserTZ($user));
        } catch (Exception $e) {
            $uf = $this->get_time_format($user);
            $GLOBALS['log']->error("fromUserTime: Conversion of $date from user format $uf failed: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Create a date object from any string
     *
     * Same formats accepted as for DateTime ctor
     *
     * @param string $date
     * @param User $user
     * @return SugarDateTime
     */
    public function fromString($date, User $user = null)
    {
        try {
            return new SugarDateTime($date, $this->_getUserTZ($user));
        } catch (Exception $e) {
            $GLOBALS['log']->error("fromString: Conversion of $date from string failed: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Create DateTime from timestamp
     *
     * @param interger|string $ts
     * @return SugarDateTime
     */
    public function fromTimestamp($ts)
    {
        return new SugarDateTime("@$ts");
    }

    /**
     * Convert DateTime to GMT timezone
     * @param DateTime $date
     * @return DateTime
     */
    public function tzGMT(DateTime $date)
    {
        return $date->setTimezone(self::$gmtTimezone);
    }

    /**
     * Convert DateTime to user timezone
     * @param DateTime $date
     * @param [User] $user
     * @return DateTime
     */
    public function tzUser(DateTime $date, User $user = null)
    {
        return $date->setTimezone($this->_getUserTZ($user));
    }

    /**
     * Get string defining midnight in current user's format
     * @param string $format Time format to use
     * @return string
     */
    protected function _get_midnight($format = null)
    {
        $zero = new DateTime("@0", self::$gmtTimezone);
        return $zero->format($format?$format:$this->get_time_format());
    }

    /**
     *
     * Basic conversion function
     *
     * @param string $date
     * @param string $fromFormat
     * @param DateTimeZone $fromTZ
     * @param string $toFormat
     * @param DateTimeZone $toTZ
     * @param bool $expand
     */
    protected function _convert($date, $fromFormat, $fromTZ, $toFormat, $toTZ, $expand = false)
    {
        $date = trim($date);
        if (empty($date)) {
            return $date;
        }
        try {
            if ($expand && strlen($date) <= 10) {
                $date = $this->expandDate($date, $fromFormat);
            }
            $phpdate = SugarDateTime::createFromFormat($fromFormat, $date, $fromTZ);
            if ($phpdate == false) {
                //		    	var_dump($date, $phpdate, $fromFormat,  DateTime::getLastErrors() );
                $GLOBALS['log']->error("convert: Conversion of $date from $fromFormat to $toFormat failed");
                return '';
            }
            if ($fromTZ !== $toTZ) {
                $phpdate->setTimeZone($toTZ);
            }
            return $phpdate->format($toFormat);
        } catch (Exception $e) {
            //	    	var_dump($date, $phpdate, $fromFormat, $fromTZ,  DateTime::getLastErrors() );
            $GLOBALS['log']->error("Conversion of $date from $fromFormat to $toFormat failed: {$e->getMessage()}");
            return '';
        }
    }

    /**
     * Convert DB datetime to local datetime
     *
     * TZ conversion is controlled by parameter
     *
     * @param string $date Original date in DB format
     * @param bool $meridiem
     * @param bool $convert_tz Perform TZ conversion?
     * @param User $user User owning the conversion formats
     * @return string Date in display format
     */
    function to_display_date_time($date, $meridiem = true, $convert_tz = true, $user = null)
    {
        return $this->_convert($date,
            self::DB_DATETIME_FORMAT, self::$gmtTimezone, $this->get_date_time_format($user),
            $convert_tz ? $this->_getUserTZ($user) : self::$gmtTimezone, true);
    }

    /**
     * Converts DB time string to local time string
     *
     * TZ conversion depends on parameter
     *
     * @param string $date Time in DB format
     * @param bool $meridiem
     * @param bool $convert_tz Perform TZ conversion?
     * @return string Time in user-defined format
     */
    public function to_display_time($date, $meridiem = true, $convert_tz = true)
    {
        if($convert_tz && strpos($date, ' ') === false) {
            // we need TZ adjustment but have no date, assume today
            $date = $this->expandTime($date, self::DB_DATETIME_FORMAT, self::$gmtTimezone);
        }
        return $this->_convert($date,
            $convert_tz ? self::DB_DATETIME_FORMAT : self::DB_TIME_FORMAT, self::$gmtTimezone,
            $this->get_time_format(), $convert_tz ? $this->_getUserTZ() : self::$gmtTimezone);
    }

    /**
     * Splits time in given format into components
     *
     * Components: h, m, s, a (am/pm) if format requires it
     * If format has am/pm, hour is 12-based, otherwise 24-based
     *
     * @param string $date
     * @param string $format
     * @return array
     */
    public function splitTime($date, $format)
    {
        if (! ($date instanceof DateTime)) {
            $date = SugarDateTime::createFromFormat($format, $date);
        }
        $ampm = strpbrk($format, 'aA');
        $datearr = array(
        	"h" => ($ampm == false) ? $date->format("H") : $date->format("h"),
        	'm' => $date->format("i"),
        	's' => $date->format("s")
        );
        if ($ampm) {
            $datearr['a'] = ($ampm{0} == 'a') ? $date->format("a") : $date->format("A");
        }
        return $datearr;
    }

    /**
     * Converts DB date string to local date string
     *
     * TZ conversion depens on parameter
     *
     * @param string $date Date in DB format
     * @param bool $convert_tz Perform TZ conversion?
     * @return string Date in user-defined format
     */
    public function to_display_date($date, $convert_tz = true)
    {
        return $this->_convert($date,
            self::DB_DATETIME_FORMAT, self::$gmtTimezone,
            $this->get_date_format(), $convert_tz ? $this->_getUserTZ() : self::$gmtTimezone, true);
    }

    /**
     * Convert date from format to format
     *
     * No TZ conversion is performed!
     *
     * @param string $date
     * @param string $fromformat Source format
     * @param string $toformat Target format
     * @return string Converted date
     */
    function to_display($date, $from, $to)
    {
        return $this->_convert($date, $from, self::$gmtTimezone, $to, self::$gmtTimezone);
    }

    /**
     * Get DB datetime format
     * @return string
     */
    public function get_db_date_time_format()
    {
        return self::DB_DATETIME_FORMAT;
    }

    /**
     * Get DB date format
     * @return string
     */
    public function get_db_date_format()
    {
        return self::DB_DATE_FORMAT;
    }

    /**
     * Get DB time format
     * @return string
     */
    public function get_db_time_format()
    {
        return self::DB_TIME_FORMAT;
    }

    /**
     * Convert date from local datetime to GMT-based DB datetime
     *
     * Includes TZ conversion.
     *
     * @param string $date
     * @return string Datetime in DB format
     */
    public function to_db($date)
    {
        return $this->_convert($date,
            $this->get_date_time_format(), $this->_getUserTZ(),
            $this->get_db_date_time_format(), self::$gmtTimezone,
            true);
    }

    /**
     * Convert local datetime to DB date
     *
     * TZ conversion depends on parameter. If false, only format conversion is performed.
     *
     * @param string $date Local date
     * @param bool $use_offset Should time and TZ be taken into account?
     * @return string Date in DB format
     */
    public function to_db_date($date, $convert_tz = true)
    {
        return $this->_convert($date,
            $this->get_date_time_format(), $convert_tz ? $this->_getUserTZ() : self::$gmtTimezone,
            self::DB_DATE_FORMAT, self::$gmtTimezone, true);
    }

    /**
     * Convert local datetime to DB time
     *
     * TZ conversion depends on parameter. If false, only format conversion is performed.
     *
     * @param string $date Local date
     * @param bool $convert_tz Should time and TZ be taken into account?
     * @return string Time in DB format
     */
    public function to_db_time($date, $convert_tz = true)
    {
        $format = $this->get_date_time_format();
        $tz = $convert_tz ? $this->_getUserTZ() : self::$gmtTimezone;
        if($convert_tz && strpos($date, ' ') === false) {
            // we need TZ adjustment but have short string, expand it to full one
            // FIXME: if the string is short, should we assume date or time?
            $date = $this->expandTime($date, $format, $tz);
        }
        return $this->_convert($date,
            $convert_tz ? $format : $this->get_time_format(),
            $tz,
            self::DB_TIME_FORMAT, self::$gmtTimezone);
    }

    /**
     * Takes a Date & Time value in local format and converts them to DB format
     * No TZ conversion!
     *
     * @param string $date
     * @param string $time
     * @return array Date & time in DB format
     **/
    public function to_db_date_time($date, $time)
    {
        try {
            $phpdate = SugarDateTime::createFromFormat($this->get_date_time_format(),
                $this->merge_date_time($date, $time), self::$gmtTimezone);
            if ($phpdate == false) {
                return array('', '');
            }
            return array($this->asDbDate($phpdate), $this->asDbTime($phpdate));
        } catch (Exception $e) {
            $GLOBALS['log']->error("Conversion of $date,$time failed");
            return array('', '');
        }
    }

    /**
     * Return current time in DB format
     * @return string
     */
    public function nowDb()
    {
        if(!$this->allow_cache) {
            $nowGMT = $this->getNow();
        } else {
            $nowGMT = $this->now;
        }
        return $this->asDb($nowGMT);
    }

    /**
     * Return current date in DB format
     * @return string
     */
    public function nowDbDate()
    {
        if(!$this->allow_cache) {
            $nowGMT = $this->getNow();
        } else {
            $nowGMT = $this->now;
        }
        return $this->asDbDate($nowGMT);
    }

    /**
     * Get 'now' DateTime object
     * @param $userTz return in user timezone?
     * @return SugarDateTime
     */
    public function getNow($userTz = false)
    {
        if(!$this->allow_cache) {
            return new SugarDateTime("now", $userTz?$this->_getUserTz():self::$gmtTimezone);
        }
        // TODO: should we return clone?
        $now = clone $this->now;
        if($userTz) {
            return $this->tzUser($now);
        }
        return $now;
    }

    /**
     * Return current datetime in local format
     * @return string
     */
    public function now()
    {
        return  $this->asUser($this->getNow());
    }

    /**
     * Return current date in User format
     * @return string
     */
    public function nowDate()
    {
        return  $this->asUserDate($this->getNow());
    }

    /**
     * Get user format's time separator
     * @return string
     */
    public function timeSeparator()
    {
        if (! empty($this->time_separator)) {
            return $this->time_separator;
        }
        $date = $this->_convert("00:11:22", self::DB_TIME_FORMAT, null, $this->get_time_format(), null);
        if (preg_match('/\d+(.+?)11/', $date, $matches)) {
            $sep = $matches[1];
        } else {
            $sep = ':';
        }
        $this->time_separator = $sep;
        return $sep;
    }

    /**
     * Returns start and end of a certain local date in GMT
     * Example: for May 19 in PDT start would be 2010-05-19 07:00:00, end would be 2010-05-20 06:59:59
     * @param string|DateTime $date Date in any suitable format
     * @return array Start & end date in start, startdate, starttime, end, enddate, endtime
     */
    public function getDayStartEndGMT($date, User $user = null)
    {
        if ($date instanceof DateTime) {
            $min = clone $date;
            $min->setTimezone($this->_getUserTZ($user));
            $max = clone $date;
            $max->setTimezone($this->_getUserTZ($user));
        } else {
            $min = new DateTime($date, $this->_getUserTZ($user));
            $max = new DateTime($date, $this->_getUserTZ($user));
        }
        $min->setTime(0, 0);
        $max->setTime(23, 59, 59);

        $min->setTimezone(self::$gmtTimezone);
        $max->setTimezone(self::$gmtTimezone);

        $result['start'] = $this->asDb($min);
        $result['startdate'] = $this->asDbDate($min);
        $result['starttime'] = $this->asDbTime($min);
        $result['end'] = $this->asDb($max);
        $result['enddate'] = $this->asDbDate($max);
        $result['endtime'] = $this->asDbtime($max);

        return $result;
    }

    public function addInterval($date, $interval)
    {
		$ndate = clone $date;
		$ndate->add(new DateInterval($interval));
		return $ndate;
    }

    /**
     * Merge time without am/pm with am/pm string
     * @TODO find better way to do this!
     *
     * @param string $date
     * @param string $format User time format
     * @param string $mer
     * @return string
     */
    function merge_time_meridiem($date, $format, $mer)
    {
        $date = trim($date);
        if (empty($date)) {
            return $date;
        }
        $fakeMerFormat = str_replace(array('a', 'A'), array('@~@', '@~@'), $format);
        $noMerFormat = str_replace(array('a', 'A'), array('', ''), $format);
        $newDate = $this->swap_formats($date, $noMerFormat, $fakeMerFormat);
        return str_replace('@~@', $mer, $newDate);
    }

    // format - date expression ('' means now) for start and end of the range
    protected $date_expressions = array(
        'yesterday' =>    array("-1 day", "-1 day"),
        'today' =>        array("", ""),
        'tomorrow' =>     array("+1 day", "+1 day"),
        'last_7_days' =>  array("-6 days", ""),
        'next_7_days' =>  array("", "+6 days"),
        'last_30_days' => array("-29 days", ""),
        'next_30_days' => array("", "+29 days"),
    );

    /**
     * Parse date template
     * @param string $template Date expression
     * @param bool $daystart Do we want start or end of the day?
     * @param User $user
     */
    protected function parseFromTemplate($template, $daystart, User $user = null)
	{
        $now = $this->tzUser($this->getNow(), $user);
        if(!empty($template[0])) {
            $now->modify($template[0]);
        }
        if($daystart) {
            return $now->get_day_begin();
        } else {
            return $now->get_day_end();
        }
	}

	/**
	 * Get month-long range mdiff months from now
	 */
	protected function diffMon($mdiff, User $user)
	{
        $now = $this->tzUser($this->getNow(), $user);
	    $now->setDate($now->year, $now->month+$mdiff, 1);
	    $start = $now->get_day_begin();
	    $end = $now->setDate($now->year, $now->month, $now->days_in_month)->setTime(23, 59, 59);
	    return array($start, $end);
	}

	/**
	 * Get year-long range ydiff years from now
	 */
	protected function diffYear($ydiff, User $user)
	{
        $now = $this->tzUser($this->getNow(), $user);
	    $now->setDate($now->year+$ydiff, 1, 1);
	    $start = $now->get_day_begin();
	    $end = $now->setDate($now->year, 12, 31)->setTime(23, 59, 59);
	    return array($start, $end);
	}

	/**
	 * Parse date range expression
	 * Returns beginning and end of the range as a date
	 * @param string $range
	 * @param User $user
	 * @return array
	 */
	public function parseDateRange($range, User $user = null)
	{
        if(isset($this->date_expressions[$range])) {
            return array($this->parseFromTemplate($this->date_expressions[$range][0], true, $user),
                $this->parseFromTemplate($this->date_expressions[$range][1], false, $user)
            );
        }
	    switch($range) {
			case 'next_month':
			    return $this->diffMon(1,  $user);
		    case 'last_month':
			    return $this->diffMon(-1,  $user);
		    case 'this_month':
			    return $this->diffMon(0,  $user);
	        case 'last_year':
			    return $this->diffYear(-1,  $user);
	        case 'this_year':
			    return $this->diffYear(0,  $user);
	        case 'next_year':
			    return $this->diffYear(1,  $user);
	        default:
			    return null;
	    }
	}

    /********************* OLD functions, should not be used publicly anymore ****************/
    /**
     * @deprecated for public use
     * Convert date from one format to another
     *
     * @param string $date
     * @param string $from
     * @param string $to
     * @return string
     */
    public function swap_formats($date, $from, $to)
    {
        return $this->_convert($date, $from, self::$gmtTimezone, $to, self::$gmtTimezone);
    }

    /**
     * @deprecated for public use
     * handles offset values for Timezones and DST
     * @param	$date	     string		date/time formatted in user's selected format
     * @param	$format	     string		destination format value as passed to PHP's date() funtion
     * @param	$to		     boolean
     * @param	$user	     object		user object from which Timezone and DST
     * @param	$usetimezone string		timezone name as it appears in timezones.php
     * values will be derived
     * @return 	 string		date formatted and adjusted for TZ and DST
     */
    function handle_offset($date, $format, $to = true, $user = null, $usetimezone = null)
    {
        $tz = empty($usetimezone)?$this->_getUserTZ($user):new DateTimeZone($usetimezone);
        $dateobj = new SugarDateTime($date, $to? self::$gmtTimezone : $tz);
        $dateobj->setTimezone($to ? $tz: self::$gmtTimezone);
        return $dateobj->format($format);
//        return $this->_convert($date, $format, $to ? self::$gmtTimezone : $tz, $format, $to ? $tz : self::$gmtTimezone);
    }

    /**
     * @deprecated for public use
     * Get current GMT datetime in DB format
     * @return string
     */
    function get_gmt_db_datetime()
    {
        return $this->nowDb();
    }

    /**
     * @deprecated for public use
     * Get current GMT date in DB format
     * @return string
     */
    function get_gmt_db_date()
    {
        return $this->nowDbDate();
    }

    /**
     * @deprecated for public use
     * this method will take an input $date variable (expecting Y-m-d format)
     * and get the GMT equivalent - with an hour-level granularity :
     * return the max value of a given locale's
     * date+time in GMT metrics (i.e., if in PDT, "2005-01-01 23:59:59" would be
     * "2005-01-02 06:59:59" in GMT metrics)
     */
    function handleOffsetMax($date)
    {
        $min = new DateTime($date, $this->_getUserTZ());
        $min->setTime(0, 0);
        $max = new DateTime($date, $this->_getUserTZ());
        $max->setTime(23, 59, 59);

        $min->setTimezone(self::$gmtTimezone);
        $max->setTimezone(self::$gmtTimezone);

        $gmtDateTime['date'] = $this->asDbDate($max, false);
        $gmtDateTime['time'] = $this->asDbDate($max, false);
        $gmtDateTime['min'] = $this->asDb($min);
        $gmtDateTime['max'] = $this->asDb($max);

        return $gmtDateTime;
    }

    /**
     * @deprecated for public use
     * this returns the adjustment for a user against the server time
     *
     * @return integer number of minutes to adjust a time by to get the appropriate time for the user
     */
    public function adjustmentForUserTimeZone()
    {
        $tz = $this->_getUserTZ();
        $server_tz = new DateTimeZone(date_default_timezone_get());
        if ($tz && $server_tz) {
            return ($server_tz->getOffset($this->now) - $tz->getOffset($this->now)) / 60;
        }
        return 0;
    }

    /**
     * @deprecated for public use
     * Create regexp from datetime format
     * @param string $format
     * @return string Regular expression string
     */
    public static function get_regular_expression($format)
    {
        $newFormat = '';
        $regPositions = array();
        $ignoreNextChar = false;
        $count = 1;
        foreach (str_split($format) as $char) {
            if (! $ignoreNextChar && isset(self::$format_to_regexp[$char])) {
                $newFormat .= '(' . self::$format_to_regexp[$char] . ')';
                $regPositions[$char] = $count;
                $count ++;
            } else {
                $ignoreNextChar = false;
                $newFormat .= $char;

            }
            if ($char == "\\") {
                $ignoreNextChar = true;
            }
        }

        return array('format' => $newFormat, 'positions' => $regPositions);
    }

    /**
     * @deprecated for public use
	 * assumes that olddatetime is in Y-m-d H:i:s format
	 */
    function convert_to_gmt_datetime($olddatetime)
    {
        if (! empty($olddatetime)) {
            return date('Y-m-d H:i:s', strtotime($olddatetime) - date('Z'));
        }
    }

    /**
     * @deprecated for public use
	 * get user timezone info
	 */
    public function getUserTimeZone(User $user = null)
    {
        $tz = $this->_getUserTZ($user);
        return array("gmtOffset" => $tz->getOffset($this->now) / 60);
    }

    /**
     * Returns the offset from user's timezone to GMT
     * @param User $user
     * @param DateTime $time When the offset is taken, default is now
     * @return int
     */
    public function getUserUTCOffset(User $user = null, DateTime $time = null)
    {
        if(empty($time)) {
            $time = $this->now;
        }
        return $this->_getUserTZ($user)->getOffset($time) / 60;
    }

    /**
     * @deprecated for public use
	 * get timezone start & end
	 */
    public function getDSTRange($year, $zone)
    {
        $year = SugarDateTime::createFromFormat("Y", $year, self::$gmtTimezone);
        $year_end = clone $year;
        $year_end->setDate((int) $year, 12, 31);
        $year_end->setTime(23, 59, 59);
        $year->setDate((int) $year, 1, 1);
        $year->setTime(0, 0, 0);
        $tz = $this->_getUserTZ();
        $transitions = $tz->getTransitions($year->getTimestamp(), $year_end->getTimestamp());
        $idx = 0;
        while (! $transitions[$idx]["isdst"])
            $idx ++;
        $startdate = new DateTime("@" . $transitions[$idx]["ts"], self::$gmtTimezone);
        while ($transitions[$idx]["isdst"])
            $idx ++;
        $enddate = new DateTime("@" . $transitions[$idx]["ts"], self::$gmtTimezone);
        return array("start" => $this->asDb($startdate), "end" => $this->asDb($enddate));
    }

/****************** GUI stuff that really shouldn't be here, will be moved ************/
    /*
	 * @todo This should return the raw text to be included within a <script> tag.
	 *	   Having this display it's own <script> keeps it from being able to be embedded
	 *	   in another Javascript file to allow for better caching
	 */
    /*
	 * TODO: Move to separate utility class
	 */
    /**
     * Get Javascript variables setup for user date format validation
     * @deprecated
     * @return string JS code
     */
    function get_javascript_validation()
    {
		$cal_date_format = $this->get_cal_date_format();
		$timereg = $this->get_regular_expression($this->get_time_format());
		$datereg = $this->get_regular_expression($this->get_date_format());
		$date_pos = '';
		foreach($datereg['positions'] as $type=>$pos) {
			if (empty($date_pos)) {
				$date_pos.= "'$type': $pos";
			} else {
				$date_pos.= ",'$type': $pos";
			}

		}
		$date_pos = '{'.$date_pos.'}';
		if (preg_match('/\)([^\d])\(/', $timereg['format'], $match)) {
			$time_separator = $match[1];
		} else {
			$time_separator = ":";
		}
		$hour_offset = $this->adjustmentForUserTimeZone() * - 60;

		$the_script = "<script type=\"text/javascript\">\n"
			."\tvar time_reg_format = '".$timereg['format']."';\n"
			."\tvar date_reg_format = '".$datereg['format']."';\n"
			."\tvar date_reg_positions = $date_pos;\n"
			."\tvar time_separator = '$time_separator';\n"
			."\tvar cal_date_format = '$cal_date_format';\n"
			."\tvar time_offset = $hour_offset;\n"
			."</script>";

		return $the_script;
    }

    /**
     * AMPMMenu
     * This method renders a <select> HTML form element based on the
     * user's time format preferences, with give date's value highlighted.
     *
     * If user's prefs have no AM/PM string, returns empty string.
     *
     * @todo There is hardcoded HTML in here that does not allow for localization
     * of the AM/PM am/pm Strings in this drop down menu.  Also, perhaps
     * change to the substr_count function calls to strpos
     * @deprecated
     * @param string $prefix Prefix for SELECT
     * @param string $date Date in display format
     * @param string $attrs Additional attributes for SELECT
     * @return string SELECT HTML
     */
    function AMPMMenu($prefix, $date, $attrs = '')
    {
        $tf = $this->get_time_format();
        $am = strpbrk($tf, 'aA');
        if ($am == false) {
            return '';
        }
        $selected = array("am" => "", "pm" => "", "AM" => "", "PM" => "");
        if (preg_match('/([ap]m)/i', $date, $match)) {
            $selected[$match[1]] = " selected";
        }

        $menu = "<select name='" . $prefix . "meridiem' " . $attrs . ">";
        if ($am{0} == 'a') {
            $menu .= "<option value='am'{$selected["am"]}>am";
            $menu .= "<option value='pm'{$selected["pm"]}>pm";
        } else {
            $menu .= "<option value='AM'{$selected["AM"]}>AM";
            $menu .= "<option value='PM'{$selected["PM"]}>PM";
        }

        return $menu . '</select>';
    }

    /**
     * TODO: REMOVE?
     */
    function get_user_date_format()
    {
        return str_replace(array('Y', 'm', 'd'), array('yyyy', 'mm', 'dd'), $this->get_date_format());
    }

    /**
     * TODO: REMOVE?
     * @deprecated
     * @return string
     */
    function get_user_time_format()
    {
        global $sugar_config;
        $time_pref = $this->get_time_format();

        if (! empty($time_pref) && ! empty($sugar_config['time_formats'][$time_pref])) {
            return $sugar_config['time_formats'][$time_pref];
        }

        return '23:00'; //default
    }

    /**
     * Expand date format by adding midnight to it
     * Note: date is assumed to be in target format already
     * @param string $date
     * @param string $format Target format
     * @return string
     */
    public function expandDate($date, $format)
    {
        $formats = $this->split_date_time($format);
        if(isset($formats[1])) {
            return $this->merge_date_time($date, $this->_get_midnight($formats[1]));
        }
        return $date;
    }

    /**
     * Expand time format by adding today to it
     * Note: time is assumed to be in target format already
     * @param string $date
     * @param string $format Target format
     * @param DateTimeZone $tz
     */
    public function expandTime($date, $format, $tz)
    {
        $formats = $this->split_date_time($format);
        if(isset($formats[1])) {
            $now = clone $this->getNow();
            $now->setTimezone($tz);
            return $this->merge_date_time($now->format($formats[0]), $date);
        }
        return $date;
    }

    /**
	 * Get midnight (start of the day) in local time format
	 *
	 * @return Time string
	 */
	function get_default_midnight()
	{
        return $this->_get_midnight($this->get_time_format());
	}

	/**
	 * Get the name of the timezone for current user
	 * @return string
	 */
	public static function userTimezone()
	{
	    $user = $GLOBALS['current_user'];
	    if(empty($user)) {
	        return '';
	    }
	    $tz = self::getInstance()->_getUserTZ($user);
	    if($tz) {
	        return $tz->getName();
	    }
	    return '';
	}

	/**
	 * Guess the timezone for the current user
	 * @return string
	 */
	public static function guessTimezone($userOffset = 0)
	{
	    if(!is_numeric($userOffset)) {
		    return '';
	    }
	    $defaultZones= array('America/New_York', 'America/Los_Angeles','America/Chicago', 'America/Denver',
	    	'America/Anchorage', 'America/Phoenix', 'Europe/Amsterdam','Europe/Athens','Europe/London',
	    	'Australia/Sydney', 'Australia/Perth');

	    $now = new DateTime();
    	if($userOffset == 0) {
    	     array_unshift($defaultZones, date('T'));
    	     $gmtOffset = date('Z');
    	} else {
    	    $gmtOffset = $userOffset * 60;
    	}
	    foreach($defaultZones as $zoneName) {
	        $tz = new DateTimeZone($zoneName);
	        if($tz->getOffset($now) == $gmtOffset) {
                return $tz->getName();
	        }
	    }
    	// try all zones
	    foreach(timezone_identifiers_list() as $zoneName) {
	        $tz = new DateTimeZone($zoneName);
	        if($tz->getOffset($now) == $gmtOffset) {
                return $tz->getName();
	        }
	    }
	    return null;
	}

	/**
	 * Get display name for a certain timezone
	 * @param string|DateTimeZone $name TZ name
	 * @return string
	 */
	public static function tzName($name)
	{
	    if(empty($name)) {
	        return '';
	    }
	    if($name instanceof DateTimeZone) {
	        $tz = $name;
	    } else {
            $tz = timezone_open($name);
	    }
        if(!$tz) {
            return "???";
        }
        $now = new DateTime("now", $tz);
        $off = $now->getOffset();
        $translated = translate('timezone_dom','',$name);
        if(is_string($translated) && !empty($translated)) {
            $name = $translated;
        }
        return sprintf("%s (GMT%+2d:%02d)%s", str_replace('_',' ', $name), $off/3600, (abs($off)/60)%60, "");//$now->format('I')==1?"(+DST)":"");
	}

	/**
	 * Get list of all timezones in the system
	 * @return array
	 */
	public static function getTimezoneList()
	{
        $now = new DateTime();
        $zones = array();
	    foreach(timezone_identifiers_list() as $zoneName) {
            $tz = new DateTimeZone($zoneName);
	        $zones[$zoneName] = $tz->getOffset($now);
	    }
	    asort($zones);
	    foreach($zones as $name => $offset) {
	        $res_zones[$name] = self::tzName($name);
	    }
	    return $res_zones;
	}

	/**
	 * Print timestamp in RFC2616 format:
	 * @return string
	 */
	public static function httpTime($ts = null)
	{
	    if($ts === null) {
	        $ts = time();
	    }
	    return gmdate(self::RFC2616_FORMAT, $ts);
	}

	/**
	 * Create datetime object from calendar array
	 * @param array $time
	 * @return SugarDateTime
	 */
	public function fromTimeArray($time)
	{
		if (! isset( $time) || count($time) == 0 )
		{
			return $this->nowDb();
		}
		elseif ( isset( $time['ts']))
		{
			return $this->fromTimestamp($time['ts']);
		}
		elseif ( isset( $time['date_str']))
		{
		    return $this->fromDb($time['date_str']);
		}
		else
		{
    		$hour = 0;
    		$min = 0;
    		$sec = 0;
    		$now = $this->getNow(true);
    		$day = $now->day;
    		$month = $now->month;
    		$year = $now->year;
		    if (isset($time['sec']))
			{
        			$sec = $time['sec'];
			}
			if (isset($time['min']))
			{
        			$min = $time['min'];
			}
			if (isset($time['hour']))
			{
        			$hour = $time['hour'];
			}
			if (isset($time['day']))
			{
        			$day = $time['day'];
			}
			if (isset($time['month']))
			{
        			$month = $time['month'];
			}
			if (isset($time['year']) && $time['year'] >= 1970)
			{
        			$year = $time['year'];
			}
			return $now->setDate($year, $month, $day)->setTime($hour, $min, $sec)->setTimeZone(self::$gmtTimezone);
		}
        return null;
	}

	/**
	 * Returns the date portion of a datetime string
	 *
	 * @param string $datetime
	 * @return string
	 */
	public function getDatePart($datetime)
	{
	    list($date, $time) = $this->split_date_time($datetime);
	    return $date;
	}

	/**
	 * Returns the time portion of a datetime string
	 *
	 * @param string $datetime
	 * @return string
	 */
	public function getTimePart($datetime)
	{
	    list($date, $time) = $this->split_date_time($datetime);
	    return $time;
	}
}

?>