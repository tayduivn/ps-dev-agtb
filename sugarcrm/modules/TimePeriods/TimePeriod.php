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
/*********************************************************************************
 * $Id: TimePeriod.php 54636 2010-02-19 02:54:46Z jmertic $
 * Description: TODO:  To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/





// User is used to store customer information.
class TimePeriod extends SugarBean {
	//time period stored fields.
	var $id;
	var $name;
	var $parent_id;
	var $start_date;
	var $end_date;
	var $created_by;
	var $date_entered;
	var $date_modified;
	var $deleted;
	var $fiscal_year;
	var $is_fiscal_year;
    var $is_fiscal;
	//end time period stored fields.
	var $table_name = "timeperiods";
	var $fiscal_year_checked;
	var $module_dir = 'TimePeriods';
    var $time_period_type = 'Annually';
	var $object_name = "TimePeriod";
	var $user_preferences;
    var $is_leaf;
	var $encodeFields = Array("name");

	// This is used to retrieve related fields from form posts.
	var $additional_column_fields = Array('reports_to_name');

	
	var $new_schema = true;

	function __construct() {
		parent::SugarBean();
		$this->disable_row_level_security =true;
	}

	function save($check_notify = false){
		//if (empty($this->id)) $this->parent_id = null;



		return parent::save($check_notify);
	}



	function get_summary_text()
	{
		return "$this->name";
	}

	
	function retrieve($id, $encode=false, $deleted=true){
		$ret = parent::retrieve($id, $encode, $deleted);
		return $ret;
	}

	function is_authenticated()
	{
		return $this->authenticated;
	}

	function fill_in_additional_list_fields() {
		$this->fill_in_additional_detail_fields();
	}

	function fill_in_additional_detail_fields()
	{
		if (isset($this->parent_id) && !empty($this->parent_id)) {
		
		  $query ="SELECT name from timeperiods where id = '$this->parent_id' and deleted = 0";
		  $result =$this->db->query($query, true, "Error filling in additional detail fields") ;
		  $row = $this->db->fetchByAssoc($result);
		  $GLOBALS['log']->debug("additional detail query results: $row");

		  
		  if($row != null) {
			 $this->fiscal_year = $row['name'];
		  }
		}
	}


	function get_list_view_data(){

		$timeperiod_fields = $this->get_list_view_array();		
		$timeperiod_fields['FISCAL_YEAR'] = $this->fiscal_year;
	
		if ($this->is_fiscal_year == 1)
			$timeperiod_fields['FISCAL_YEAR_CHECKED'] = "checked";
		
		return $timeperiod_fields;
	}

	function list_view_parse_additional_sections(&$list_form, $xTemplateSection){
		return $list_form;
	}

	function create_export_query($order_by, $where)
	{
		$query = "SELECT
				timeperiods.*";
		$query .= " FROM timeperiods ";

		$where_auto = " timeperiods.deleted = 0";

		if($where != "")
			$query .= " WHERE $where AND " . $where_auto;
		else
			$query .= " WHERE " . $where_auto;

		if($order_by != "")
			$query .= " ORDER BY $order_by";
		else
			$query .= " ORDER BY timeperiods.name";

		return $query;
	}

    /**
     * creates a new AnnualTimePeriod to start to use
     *
     * @return AnnualTimePeriod
     */
    public function createNextTimePeriod() {
        $timedate = TimeDate::getInstance();
        $nextStartDate = $timedate->fromUserDate($this->end_date);
        $nextStartDate = $nextStartDate->modify('+1 day');
        $nextPeriod = BeanFactory::newBean()
        $nextPeriod = new AnnualTimePeriod($timedate->asUserDate($nextStartDate));
        $nextPeriod->save();

        return $nextPeriod;
    }


	//Fiscal year domain is stored in the timeperiods table, and not statically defined like the rest of the
	//domains, This method builds the domain array.
	static function get_fiscal_year_dom() {

		static $fiscal_years;

		if (!isset($fiscal_years)) {

			$query = 'select id, name from timeperiods where deleted=0 and is_fiscal_year = 1 order by name';
			$db = DBManagerFactory::getInstance();
			$result = $db->query($query,true," Error filling in fiscal year domain: ");

			while (($row  =  $db->fetchByAssoc($result)) != null) {

				$fiscal_years[$row['id']]=$row['name'];
			}
			
			if (!isset($fiscal_years)) {
				$fiscal_years=array();
			}
		}
		return $fiscal_years;
	}


    /**
     * getTimePeriod
     * @param
     */
    static function getTimePeriod($timedate=null)
    {
        global $app_strings;
        $timedate = !is_null($timedate) ? $timedate : TimeDate::getInstance();
        //get current timeperiod
        $db = DBManagerFactory::getInstance();
        $queryDate = $timedate->getNow();
        $date = $db->convert($db->quoted($queryDate->asDbDate()), 'date');
        $timeperiod_id = $db->getOne("SELECT id FROM timeperiods WHERE start_date < {$date} AND end_date > {$date} and is_fiscal_year = 0", false, string_format($app_strings['ERR_TIMEPERIOD_UNDEFINED_FOR_DATE'], array($queryDate->asDbDate())));


        if(!empty($timeperiod_id))
        {
           $timeperiod = new TimePeriod();
           $timeperiod->retrieve($timeperiod_id);
           return $timeperiod;
        }

        return null;
    }


    /**
     * Return a timeperiod object for a given database date
     *
     * @param $db_date String value of database date (ex: 2012-12-30)
     * @return bool|TimePeriod TimePeriod instance for corresponding database date; false if nothing found
     */
    public static function retrieveFromDate($db_date) {
        global $app_strings;
        $db = DBManagerFactory::getInstance();
        $db_date = $db->quote($db_date);
        $timeperiod_id = $db->getOne("SELECT id FROM timeperiods WHERE start_date < '{$db_date}' AND end_date > '{$db_date}' and is_fiscal_year = 0", false, string_format($app_strings['ERR_TIMEPERIOD_UNDEFINED_FOR_DATE'], array($db_date)));

        if(!empty($timeperiod_id)) {
            return BeanFactory::getBean('TimePeriods', $timeperiod_id);
        }

        return false;
    }



    /**
     * getCurrentName
     *
     * Returns the current timeperiod name if a timeperiod entry is found
     *
     */
    static function getCurrentName($timedate=null)
    {
        global $app_strings;
        $timedate = !is_null($timedate) ? $timedate : TimeDate::getInstance();
        //get current timeperiod
        $db = DBManagerFactory::getInstance();
        $queryDate = $timedate->getNow();
        $date = $db->convert($db->quoted($queryDate->asDbDate()), 'date');
        $timeperiod = $db->getOne("SELECT name FROM timeperiods WHERE start_date < {$date} AND end_date > {$date} and is_fiscal_year = 0", false, string_format($app_strings['ERR_TIMEPERIOD_UNDEFINED_FOR_DATE'], array($queryDate->asDbDate())));
        $timeperiods = array();
        if(!empty($timeperiod))
        {
            $timeperiods[$timeperiod] = $app_strings['LBL_CURRENT_TIMEPERIOD'];
        }
        return $timeperiods;
    }

    /**
     * getCurrentId
     *
     * Returns the current timeperiod name if a timeperiod entry is found
     *
     */
    static function getCurrentId($timedate=null)
    {
        static $currentId;

        if(!isset($currentId))
        {
            global $app_strings;
            $timedate = !is_null($timedate) ? $timedate : TimeDate::getInstance();
            //get current timeperiod
            $db = DBManagerFactory::getInstance();
            $queryDate = $timedate->getNow();
            $date = $db->convert($db->quoted($queryDate->asDbDate()), 'date');
            $currentId = $db->getOne("SELECT id FROM timeperiods WHERE start_date < {$date} AND end_date > {$date} and is_fiscal_year = 0", false, string_format($app_strings['ERR_TIMEPERIOD_UNDEFINED_FOR_DATE'], array($queryDate->asDbDate())));
        }
        return $currentId;
    }

    /**
     * getLastCurrentNextIds
     * Returns the quarterly ids of the last, current and next timeperiod
     * @static
     * @param $timedate Optional TimeDate instance to calculate values off of
     * @return $ids Mixed array of id=>name value(s) depending on the current system date or timedate parameter (if supplied)
     */
    static function getLastCurrentNextIds($timedate=null)
    {
        global $app_strings;
        $timedate = !is_null($timedate) ? $timedate : TimeDate::getInstance();
        $timeperiods = array();

        //get current timeperiod
        $db = DBManagerFactory::getInstance();
        $queryDate = $timedate->getNow();
        $date = $db->convert($db->quoted($queryDate->asDbDate()), 'date');
        $timeperiod = $db->getOne("SELECT id FROM timeperiods WHERE start_date < {$date} AND end_date > {$date} and is_fiscal_year = 0", false, string_format($app_strings['ERR_TIMEPERIOD_UNDEFINED_FOR_DATE'], array($queryDate->asDbDate())));

        if(!empty($timeperiod))
        {
            $timeperiods[$timeperiod] = $app_strings['LBL_CURRENT_TIMEPERIOD'];
        }

        //previous timeperiod (3 months ago)
        $queryDate = $queryDate->modify('-3 month');
        $date = $db->convert($db->quoted($queryDate->asDbDate()), 'date');
        $timeperiod = $db->getOne("SELECT id FROM timeperiods WHERE start_date < {$date} AND end_date > {$date} and is_fiscal_year = 0", false, string_format($app_strings['ERR_TIMEPERIOD_UNDEFINED_FOR_DATE'], array($queryDate->asDbDate())));

        if(!empty($timeperiod))
        {
            $timeperiods[$timeperiod] = $app_strings['LBL_PREVIOUS_TIMEPERIOD'];
        }

        //next timeperiod (3 months from today)
        $queryDate = $queryDate->modify('+6 month');
        $date = $db->convert($db->quoted($queryDate->asDbDate()), 'date');
        $timeperiod = $db->getOne("SELECT id FROM timeperiods WHERE start_date < {$date} AND end_date > {$date} and is_fiscal_year = 0", false, string_format($app_strings['ERR_TIMEPERIOD_UNDEFINED_FOR_DATE'], array($queryDate->asDbDate())));

        if(!empty($timeperiod))
        {
            $timeperiods[$timeperiod] = $app_strings['LBL_NEXT_TIMEPERIOD'];
        }
        return $timeperiods;
    }

    /**
     * get_timeperiods_dom
     * @static
     * @return array
     */
    static function get_timeperiods_dom()
    {
        static $timeperiods;

        if(!isset($timeperiods))
        {
            $db = DBManagerFactory::getInstance();
            $timeperiods = array();
            $result = $db->query('SELECT id, name FROM timeperiods WHERE deleted=0');
            while(($row = $db->fetchByAssoc($result)))
            {
                if(!isset($timeperiods[$row['id']]))
                {
                    $timeperiods[$row['id']]=$row['name'];
                }
            }
        }
        return $timeperiods;
    }

    static function get_not_fiscal_timeperiods_dom()
    {
        static $not_fiscal_timeperiods;

        if(!isset($not_fiscal_timeperiods))
        {
            $db = DBManagerFactory::getInstance();
            $not_fiscal_timeperiods = array();
            $result = $db->query('SELECT id, name FROM timeperiods WHERE is_fiscal_year = 0 AND deleted=0');
            while(($row = $db->fetchByAssoc($result)))
            {
                if(!isset($not_fiscal_timeperiods[$row['id']]))
                {
                    $not_fiscal_timeperiods[$row['id']]=$row['name'];
                }
            }
        }
        return $not_fiscal_timeperiods;
    }

    /**
     * Takes the current time period and finds the next one that is in the db of the same type.  If none exists it returns null
     *
     * @return mixed
     */
    public function getNextTimePeriod() {
        $timedate = TimeDate::getInstance();

        $query = "select id, time_period_type from timeperiods where ";
        $query .= " time_period_type = " . $this->db->quoted($this->time_period_type);
        $query .= " AND deleted = 0";

        $queryDate = $timedate->fromUserDate($this->end_date);
        $queryDate = $queryDate->modify('+1 day');
        $queryDate = $this->db->convert($this->db->quoted($queryDate->asDbDate()), 'date');

        $query .= " AND start_date = {$queryDate}";

        $result = $this->db->query($query);
        $row = $this->db->fetchByAssoc($result);

        if($row == null) {
            return $this->createNextTimePeriod();
        }

        return BeanFactory::getBean($row['time_period_type'].'TimePeriods', $row['id']);

    }


    /**
     * Grabs the time period previous of this one and returns it.  If none is found, it returns null
     *
     * @return null|SugarBean
     */
    public function getPreviousTimePeriod() {
        $db = DBManagerFactory::getInstance();
        $timedate = TimeDate::getInstance();

        $query = "select id from timeperiods where ";
        $query .= " time_period_type = " . $this->db->quoted($this->time_period_type);
        $query .= " AND deleted = 0";

        $queryDate = $timedate->fromUserDate($this->end_date);
        $queryDate = $queryDate->modify('-1 day');
        $queryDate = $this->db->convert($this->db->quoted($queryDate->asDbDate()), 'date');

        $query .= " AND end_date = {$queryDate}";

        $result = $this->db->query($query);
        $row = $this->db->fetchByAssoc($result);

        if($row == null) {
            return null;
        }

        return BeanFactory::getBean($row['time_period_type'].'TimePeriods', $row['id']);
    }

    /**
     * subtracts the end from the start date to return the date length in days
     *
     * @return mixed
     */
    public function getLengthInDays() {
        $timedate = TimeDate::getInstance();
        $startDate = $timedate->fromUserDate($this->start_date);
        $endDate = $timedate->fromUserDate($this->end_date);
        $diff = $startDate->diff($endDate);
        return $diff->days + 1;
    }
}

function get_timeperiods_dom()
{
    return TimePeriod::get_timeperiods_dom();
}

function get_not_fiscal_timeperiods_dom()
{
    return TimePeriod::get_not_fiscal_timeperiods_dom();
}
