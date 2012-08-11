<?php
if ( !defined('sugarEntry') || !sugarEntry ) {
	die('Not A Valid Entry Point');
}
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
 * $Id: Forecast.php 47223 2009-05-16 02:57:14Z eddy $
 * Description: TODO:  To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

// User is used to store Forecast information.
class Forecast extends SugarBean
{

	var $id;
	var $user_id;
	var $forecast_type;
	var $opp_count;
	var $opp_weigh_value;
	var $likely_case;
	var $current;
	var $timeperiod_id;
	var $name;
	var $start_date;
	var $end_date;
	var $date_modified;
	var $best_case;
	var $worst_case;

	var $currency;
	var $currencysymbol;
	var $currency_id;

	var $table_name = "forecasts";

	var $object_name = "Forecast";
	var $user_preferences;

	var $encodeFields = Array();

	// This is used to retrieve related fields from form posts.
	var $additional_column_fields = Array('');


	var $new_schema = true;
	var $module_dir = 'Forecasts';


	function Forecast()
	{
		global $current_user;
		parent::SugarBean();
		$this->setupCustomFields('Forecasts'); //parameter is module name
		$this->disable_row_level_security = true;

		$this->currency = new Currency();
		if ( isset($current_user) ) {
			$this->currency->retrieve($current_user->getPreference('currency'));
		}
		else {
			$this->currency->retrieve('-99');
		}
		$this->currencysymbol = $this->currency->symbol;
	}


	function get_summary_text()
	{
		return "$this->name";
	}


	function retrieve( $id, $encode = false, $deleted = true )
	{
		$ret = parent::retrieve($id, $encode, $deleted);

		return $ret;
	}


	function is_authenticated()
	{
		return $this->authenticated;
	}


	function fill_in_additional_list_fields()
	{
		if ( isset($this->best_case) && !empty($this->best_case) ) {
			$this->best_case = $this->currency->convertFromDollar($this->best_case);
		}
		if ( isset($this->worst_case) && !empty($this->worst_case) ) {
			$this->worst_case = $this->currency->convertFromDollar($this->worst_case);
		}
		if ( isset($this->likely_case) && !empty($this->likely_case) ) {
			$this->likely_case = $this->currency->convertFromDollar($this->likely_case);
		}
		$this->weigh_value = ' ';
		if ( isset($this->weigh_value) && !empty($this->weigh_value) ) {
			$this->weigh_value = $this->currency->convertFromDollar($this->weigh_value);
		}
	}


	function fill_in_additional_detail_fields()
	{
	}


	function list_view_parse_additional_sections( &$list_form, $xTemplateSection )
	{
		return $list_form;
	}


	function create_export_query( $order_by, $where )
	{
		$query = "SELECT
				forecasts.*";
		$query .= " FROM forecasts ";
		$where_auto = '1=1';
		if ( empty($show_deleted) ) {
			$where_auto = " forecasts.deleted = 0";
		}
		else {
			if ( $show_deleted == 1 ) {
				$where_auto = " forecasts.deleted = 1";
			}
		}

		if ( $where != "" ) {
			$query .= " WHERE $where AND " . $where_auto;
		}
		else {
			$query .= " WHERE " . $where_auto;
		}

		if ( $order_by != "" ) {
			$query .= " ORDER BY $order_by";
		}
		else {
			$query .= " ORDER BY forecasts.date_entered desc";
		}

		return $query;
	}


	/**
	 * Return the list query used by the list views and export button. Next generation of create_new_list_query function.
	 *
	 * Override this function to return a custom query.
	 *
	 * @param string  $order_by     custom order by clause
	 * @param string  $where        custom where clause
	 * @param array   $filter       Optional (not implemented)
	 * @param array   $params       Optional (not implemented)
	 * @param int     $show_deleted Optional, default 0, show deleted records is set to 1.
	 * @param string  $join_type    (not implemented)
	 * @param boolean $return_array Optional, default false, response as array
	 * @param object  $parentbean   creating a sub-query for this bean (not implemented)
	 * @param boolean $singleSelect Optional, default false (not implemented)
	 *
	 * @return String select query string, optionally an array value will be returned if $return_array= true.
	 */
	function create_new_list_query( $order_by, $where, $filter = array(), $params = array(), $show_deleted = 0, $join_type = '', $return_array = false, $parentbean = NULL, $singleSelect = false )
	{
		global $current_user;
		$ret_array           = array();
		$ret_array['select'] = "SELECT tp.name timeperiod_name, tp.start_date start_date, tp.end_date end_date, forecasts.* ";
		$ret_array['from']   = " FROM forecasts LEFT JOIN timeperiods tp on forecasts.timeperiod_id = tp.id  ";
		$ret_array['where']  = !empty($where) ? ' WHERE ' . $where : '';

		//if order by just has asc or des
		$temp_order = trim($order_by);
		$temp_order = strtolower($temp_order);
		if ( $temp_order == 'asc' || $temp_order == 'desc' ) {
			$order_by = '';
		}

		$ret_array['order_by'] = !empty($order_by) ? ' ORDER BY ' . $order_by : '  ORDER BY forecasts.date_entered desc';

		if ( $return_array ) {
			return $ret_array;
		}

		return $ret_array['select'] . $ret_array['from'] . $ret_array['where'] . $ret_array['order_by'];
	}


	function get_list_view_data()
	{
		$forecast_fields = $this->get_list_view_array();

		global $timedate;
		$forecast_fields['START_DATE']      = $forecast_fields['START_DATE'];
		$forecast_fields['END_DATE']        = $forecast_fields['END_DATE'];
		$forecast_fields['LIKELY_CASE']     = format_number($forecast_fields['LIKELY_CASE'], 0, 0);
		$forecast_fields['BEST_CASE']       = format_number($forecast_fields['BEST_CASE'], 0, 0);
		$forecast_fields['WORST_CASE']      = format_number($forecast_fields['WORST_CASE'], 0, 0);
		$forecast_fields['OPP_WEIGH_VALUE'] = format_number($forecast_fields['OPP_WEIGH_VALUE'], 0, 0);

		return $forecast_fields;
	}


	/**
	 * Retrieve forecast data for user given a timeperiod.  By default uses the currently logged-in
	 * user and the current timeperiod.
	 * 
	 * @param String $user_id
	 * @param String $timeperiod_id
	 * @param bool   $should_rollup     False to use direct numbers, true to use rollup.
	 */
	function getForecastForUser($user_id=NULL, $timeperiod_id, $should_rollup=FALSE)
	{
		global $current_user;
		if ( is_null($user_id) ) {
			$user_id = $current_user->id;
		}
		
		$where = "user_id='$user_id'";
		
		if ( $should_rollup ) {
			$where .= " AND forecast_type='Rollup'";
		} else {
			$where .= " AND forecast_type='Direct'";
		}
		
		if ( !is_null($timeperiod_id)  ) {
			$where .= " AND timeperiod_id='$timeperiod_id'";
		} else {
			$where .= " AND timeperiod_id='" . TimePeriod::getCurrentId() . "'";
		}
		
		$query = $this->create_new_list_query(NULL, $where);
		
		$result = $this->db->query($query, true, 'Error retrieving user forecast information: ');
		
		return $this->db->fetchByAssoc($result);
	}


	function bean_implements( $interface )
	{
		switch ( $interface ) {
			case 'ACL':
				return true;
		}
		return false;
	}
}
function getTimePeriodsDropDownForForecasts(){
    return TimePeriod::get_timeperiods_dom();
}
?>
