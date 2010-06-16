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
require_once('include/generic/SugarWidgets/SugarWidgetFielddatetime.php');

class SugarWidgetFieldDate extends SugarWidgetFieldDateTime
{
        function & displayList($layout_def)
        {
            global $timedate;
            // i guess qualifier and column_function are the same..
            if (! empty($layout_def['column_function']))
             {
                $func_name = 'displayList'.$layout_def['column_function'];
                if ( method_exists($this,$func_name))
                {
                        $display = $this->$func_name($layout_def);
                        return $display;
                }
            }
            $content = $this->displayListPlain($layout_def);
    		return $content;         
        }


function get_date_part($date_time_value){

	$date_parts=explode(' ', $date_time_value);
	if (count($date_parts) == 2) {
		$date=$date_parts[0];
	} else {
		$date=$date_time_value;
	}                
	return $date;
}

function get_db_date($days,$time) {
    global $timedate;

    $begin = date($GLOBALS['timedate']->get_db_date_time_format(), time()+(86400 * $days));  //gmt date with day adjustment applied.
//	$begin = $timedate->handle_offset($begin, $timedate->get_db_date_time_format(), false, $this->assigned_user);
    
    if ($time=='start') {
        $begin_parts = explode(' ', $begin);
        $begin = $begin_parts[0] . ' 00:00:00';
    }
    else if ($time=='end') {
        $begin_parts = explode(' ', $begin);
        $begin = $begin_parts[0] . ' 23:59:59';
    } 
    return $begin;
}
function get_time_part($date_time_value) {
	$date_parts=explode(' ', $date_time_value);
	if (count($date_parts) == 2) {
		$time=$date_parts[1];
	} else {
		$time=$date_time_value;
	}                
	return $time;

}
 function queryFilterBefore_old(&$layout_def)
 {
  global $timedate;

//BEGIN SUGARCRM flav=ent ONLY
    if($this->reporter->db->dbType == 'oci8')
    {
			return $this->_get_column_select($layout_def).
          "> TO_DATE('".
          $this->reporter->db->quote($layout_def['input_name0']).
          "', 'yyyy-mm-dd')\n";
		} else {
//END SUGARCRM flav=ent ONLY
			return $this->_get_column_select($layout_def)."<'".$this->reporter->db->quote($layout_def['input_name0'])."'\n";
//BEGIN SUGARCRM flav=ent ONLY
		}
//END SUGARCRM flav=ent ONLY
 }

 function queryFilterAfter_old(&$layout_def)
 {
  global $timedate;

//BEGIN SUGARCRM flav=ent ONLY
    if($this->reporter->db->dbType == 'oci8')
    {

			return $this->_get_column_select($layout_def).
          "> TO_DATE('".
          $this->reporter->db->quote($layout_def['input_name0']).
          "', 'yyyy-mm-dd')\n";
		} else {
//END SUGARCRM flav=ent ONLY
  		return $this->_get_column_select($layout_def).">'".$this->reporter->db->quote($layout_def['input_name0'])."'\n";
//BEGIN SUGARCRM flav=ent ONLY
		}
//END SUGARCRM flav=ent ONLY
 }

 function queryFilterBetween_Dates_old(&$layout_def)
 {
  global $timedate;

//BEGIN SUGARCRM flav=ent ONLY
    if($this->reporter->db->dbType == 'oci8')
    {

			return "(".$this->_get_column_select($layout_def).">=TO_DATE('".$this->reporter->db->quote($layout_def['input_name0'])."','yyyy-mm-dd') AND \n".  $this->_get_column_select($layout_def)."<=TO_DATE('".$this->reporter->db->quote($layout_def['input_name1'])."','yyyy-mm-dd'))\n";
		} else {
//END SUGARCRM flav=ent ONLY
			return "(".$this->_get_column_select($layout_def).">='".$this->reporter->db->quote($layout_def['input_name0'])."' AND \n".  $this->_get_column_select($layout_def)."<='".$this->reporter->db->quote($layout_def['input_name1'])."')\n";
//BEGIN SUGARCRM flav=ent ONLY
		}
//END SUGARCRM flav=ent ONLY
 }

    function queryFilterNot_Equals_str(& $layout_def) {
        global $timedate;
        
        $begin = $layout_def['input_name0'];

        if ($this->reporter->db->dbType == 'oci8') {
        //BEGIN SUGARCRM flav=ent ONLY

            return "NVL( TO_CHAR(".$this->_get_column_select($layout_def)."),'0')  = '0' OR \n(".$this->_get_column_select($layout_def)."!= TO_DATE('".$this->reporter->db->quote($begin)."','yyyy-mm-dd') )\n";
            //END SUGARCRM flav=ent ONLY
        } elseif($this->reporter->db->dbType == 'mssql') {
            return "".$this->_get_column_select($layout_def)."!='".$this->reporter->db->quote($begin)."'\n";
        }else{
            return "ISNULL(".$this->_get_column_select($layout_def).") OR \n(".$this->_get_column_select($layout_def)."!='".$this->reporter->db->quote($begin)."')\n";
        }

    }
    

    function queryFilterOn(& $layout_def) {
        global $timedate;
        
        $begin = $layout_def['input_name0'];

        //BEGIN SUGARCRM flav=ent ONLY
        if ($this->reporter->db->dbType == 'oci8') {
            return $this->_get_column_select($layout_def)."=TO_DATE('".$this->reporter->db->quote($begin)."','YYYY-MM-DD')\n";
        } else {
            //END SUGARCRM flav=ent ONLY
            return $this->_get_column_select($layout_def)."='".$this->reporter->db->quote($begin)."'\n";
            //BEGIN SUGARCRM flav=ent ONLY
        }
        //END SUGARCRM flav=ent ONLY
    }
    function queryFilterBefore(& $layout_def) {
        global $timedate;
        
        $begin = $layout_def['input_name0'];

        //BEGIN SUGARCRM flav=ent ONLY
        if ($this->reporter->db->dbType == 'oci8') {
            return $this->_get_column_select($layout_def)."< TO_DATE('".$this->reporter->db->quote($begin)."', 'yyyy-mm-dd')\n";
        } else {
            //END SUGARCRM flav=ent ONLY
            return $this->_get_column_select($layout_def)."<'".$this->reporter->db->quote($begin)."'\n";
            //BEGIN SUGARCRM flav=ent ONLY
        }
        //END SUGARCRM flav=ent ONLY

    }
    
    function queryFilterAfter(& $layout_def) {
        global $timedate;

        $begin = $layout_def['input_name0'];

        //BEGIN SUGARCRM flav=ent ONLY
        if ($this->reporter->db->dbType == 'oci8') {
            return $this->_get_column_select($layout_def)."> TO_DATE('".$this->reporter->db->quote($begin)."', 'yyyy-mm-dd')\n";
        } else {
            //END SUGARCRM flav=ent ONLY
            return $this->_get_column_select($layout_def).">'".$this->reporter->db->quote($begin)."'\n";
            //BEGIN SUGARCRM flav=ent ONLY
        }
        //END SUGARCRM flav=ent ONLY
    }
    function queryFilterBetween_Dates(& $layout_def) {
        global $timedate;
        
        $begin = $layout_def['input_name0'];
        $end = $layout_def['input_name1'];

        //BEGIN SUGARCRM flav=ent ONLY
        if ($this->reporter->db->dbType == 'oci8') {
            return "(".$this->_get_column_select($layout_def).">=TO_DATE('".$this->reporter->db->quote($begin)."','yyyy-mm-dd') AND \n ".$this->_get_column_select($layout_def)."<=TO_DATE('".$this->reporter->db->quote($end)."','yyyy-mm-dd'))\n";
        } else {
            //END SUGARCRM flav=ent ONLY
            return "(".$this->_get_column_select($layout_def).">='".$this->reporter->db->quote($begin)."' AND \n".$this->_get_column_select($layout_def)."<='".$this->reporter->db->quote($end)."')\n";
            //BEGIN SUGARCRM flav=ent ONLY
        }
        //END SUGARCRM flav=ent ONLY
    }
    
	function queryFilterTP_yesterday(& $layout_def) {
		global $timedate, $current_user;
		
        $begin_timestamp = time() - 86400;
        $begin = gmdate($GLOBALS['timedate']->get_db_date_time_format(), $begin_timestamp);
		$begin = $timedate->handle_offset($begin, $timedate->get_db_date_time_format(), true, $this->assigned_user);

        $begin_parts = explode(' ', $begin);
        $begin = $begin_parts[0] . ' 00:00:00';
        $end = $begin_parts[0] . ' 23:59:59';
        return $this->get_start_end_date_filter($layout_def,$begin,$end);

	}
	function queryFilterTP_today(& $layout_def) {
		global $timedate, $current_user;
        
        $begin_timestamp = time();
        $begin = gmdate($GLOBALS['timedate']->get_db_date_time_format(), $begin_timestamp);
		$begin = $timedate->handle_offset($begin, $timedate->get_db_date_time_format(), true, $this->assigned_user);
        
        $begin_parts = explode(' ', $begin);
        $begin = $begin_parts[0] . ' 00:00:00';
        $end = $begin_parts[0] . ' 23:59:59';
        return $this->get_start_end_date_filter($layout_def,$begin,$end);

	}

	function queryFilterTP_tomorrow(& $layout_def) {
		global $timedate, $current_user;

        $begin_timestamp = time() + 86400;
        $begin = gmdate($GLOBALS['timedate']->get_db_date_time_format(), $begin_timestamp);
		$begin = $timedate->handle_offset($begin, $timedate->get_db_date_time_format(), true, $this->assigned_user);

        $begin_parts = explode(' ', $begin);
        $begin = $begin_parts[0] . ' 00:00:00';
        $end = $begin_parts[0] . ' 23:59:59';
        return $this->get_start_end_date_filter($layout_def,$begin,$end);


	}
	function queryFilterTP_last_7_days(& $layout_def) {
		global $timedate, $current_user;

        $begin_timestamp = time() - (6 * 86400);
        $begin = gmdate($GLOBALS['timedate']->get_db_date_time_format(), $begin_timestamp);
		$begin = $timedate->handle_offset($begin, $timedate->get_db_date_time_format(), true, $this->assigned_user);
        $end_timestamp = time();
        $end = gmdate($GLOBALS['timedate']->get_db_date_time_format(), $end_timestamp);
		$end = $timedate->handle_offset($end, $timedate->get_db_date_time_format(), true, $this->assigned_user);

        $begin_parts = explode(' ', $begin);
        $begin = $begin_parts[0] . ' 00:00:00';
        $end_parts = explode(' ', $end);
        $end = $end_parts[0] . ' 23:59:59';

        return $this->get_start_end_date_filter($layout_def,$begin,$end);

	}

	function queryFilterTP_next_7_days(& $layout_def) {
		global $timedate, $current_user;

        $begin_timestamp = time();
        $begin = gmdate($GLOBALS['timedate']->get_db_date_time_format(), $begin_timestamp);
		$begin = $timedate->handle_offset($begin, $timedate->get_db_date_time_format(), true, $this->assigned_user);
        $end_timestamp = time() + (86400*6);
        $end = gmdate($GLOBALS['timedate']->get_db_date_time_format(), $end_timestamp);
		$end = $timedate->handle_offset($end, $timedate->get_db_date_time_format(), true, $this->assigned_user);

        $begin_parts = explode(' ', $begin);
        $begin = $begin_parts[0] . ' 00:00:00';
        $end_parts = explode(' ', $end);
        $end = $end_parts[0] . ' 23:59:59';

        return $this->get_start_end_date_filter($layout_def,$begin,$end);
	}

	function queryFilterTP_last_month(& $layout_def) {

		global $timedate;

        $begin_timestamp = time();
        $begin = gmdate($GLOBALS['timedate']->get_db_date_time_format(), $begin_timestamp);
		$begin = $timedate->handle_offset($begin, $timedate->get_db_date_time_format(), true, $this->assigned_user);

        $begin_parts = explode(' ', $begin);
        $curr_date = explode('-',$begin_parts[0]);

		//Get year and month from time stamp.
		$curr_year=$curr_date[0];
		$curr_month=$curr_date[1];

		//get start date for last month and convert it to gmt and db format.
	    $begin=date($GLOBALS['timedate']->get_db_date_time_format(),strtotime("-1 month",mktime(0,0,0,$curr_month,1,$curr_year)));

	    //get end date for last month  and convert it to gmt and db format.
        $end=date($GLOBALS['timedate']->get_db_date_time_format(),strtotime("-1 day",mktime(23,59,59,$curr_month,1,$curr_year)));
		return $this->get_start_end_date_filter($layout_def,$begin,$end);
	}

	function queryFilterTP_this_month(& $layout_def) {

		global $timedate;

        $begin_timestamp = time();
        $begin = gmdate($GLOBALS['timedate']->get_db_date_time_format(), $begin_timestamp);
		$begin = $timedate->handle_offset($begin, $timedate->get_db_date_time_format(), true, $this->assigned_user);

        $begin_parts = explode(' ', $begin);
        $curr_date = explode('-',$begin_parts[0]);

		//Get year and month from time stamp.
		$curr_year=$curr_date[0];
		$curr_month=$curr_date[1];

		//get start date for this month and convert it to gmt and db format.
	    $begin=date($GLOBALS['timedate']->get_db_date_time_format(),mktime(0,0,0,$curr_month,1,$curr_year));

	    //get end date for this month  and convert it to gmt and db format.
	    //first get the first day of next month and move back by one day.
		if ($curr_month==12) {
			$curr_month=1;
			$curr_year+=1;
		} else {
			$curr_month+=1;
		}
        $end=date($GLOBALS['timedate']->get_db_date_time_format(),strtotime("-1 day",mktime(23,59,59,$curr_month,1,$curr_year)));
		return $this->get_start_end_date_filter($layout_def,$begin,$end);
	}

	function queryFilterTP_next_month(& $layout_def) {
		global $timedate;

        $begin_timestamp = time();
        $begin = gmdate($GLOBALS['timedate']->get_db_date_time_format(), $begin_timestamp);
		$begin = $timedate->handle_offset($begin, $timedate->get_db_date_time_format(), true, $this->assigned_user);

        $begin_parts = explode(' ', $begin);
        $curr_date = explode('-',$begin_parts[0]);

		//Get year and month from time stamp.
		$curr_year=$curr_date[0];
		$curr_month=$curr_date[1];

		if ($curr_month==12) {
			$curr_month=1;
			$curr_year+=1;
		} else {
			$curr_month+=1;
		}

		//get start date for next month and convert it to gmt and db format.
	    $begin=date($GLOBALS['timedate']->get_db_date_time_format(),mktime(0,0,0,$curr_month,1,$curr_year));
        $end=date($GLOBALS['timedate']->get_db_date_time_format(),strtotime("-1 day",(strtotime("1 month",mktime(23,59,59,$curr_month,1,$curr_year)))));

		return $this->get_start_end_date_filter($layout_def,$begin,$end);
	}

	function queryFilterTP_last_30_days(& $layout_def) {
		global $timedate;

        $begin_timestamp = time() - (29 * 86400);
        $begin = gmdate($GLOBALS['timedate']->get_db_date_time_format(), $begin_timestamp);
		$begin = $timedate->handle_offset($begin, $timedate->get_db_date_time_format(), true, $this->assigned_user);
        $end_timestamp = time();
        $end = gmdate($GLOBALS['timedate']->get_db_date_time_format(), $end_timestamp);
		$end = $timedate->handle_offset($end, $timedate->get_db_date_time_format(), true, $this->assigned_user);

        $begin_parts = explode(' ', $begin);
        $begin = $begin_parts[0] . ' 00:00:00';
        $end_parts = explode(' ', $end);
        $end = $end_parts[0] . ' 23:59:59';
        
        return $this->get_start_end_date_filter($layout_def,$begin,$end);
	}

	function queryFilterTP_next_30_days(& $layout_def) {
		global $timedate;

        $begin_timestamp = time();
        $begin = gmdate($GLOBALS['timedate']->get_db_date_time_format(), $begin_timestamp);
		$begin = $timedate->handle_offset($begin, $timedate->get_db_date_time_format(), true, $this->assigned_user);
        $end_timestamp = time() + (29 * 86400);
        $end = gmdate($GLOBALS['timedate']->get_db_date_time_format(), $end_timestamp);
		$end = $timedate->handle_offset($end, $timedate->get_db_date_time_format(), true, $this->assigned_user);

        $begin_parts = explode(' ', $begin);
        $begin = $begin_parts[0] . ' 00:00:00';
        $end_parts = explode(' ', $end);
        $end = $end_parts[0] . ' 23:59:59';

        return $this->get_start_end_date_filter($layout_def,$begin,$end);
	}


	function queryFilterTP_this_quarter(& $layout_def) {
	}

	function queryFilterTP_last_year(& $layout_def) {

		global $timedate;

        $begin_timestamp = time();
        $begin = gmdate($GLOBALS['timedate']->get_db_date_time_format(), $begin_timestamp);
		$begin = $timedate->handle_offset($begin, $timedate->get_db_date_time_format(), true, $this->assigned_user);

        $begin_parts = explode(' ', $begin);
        $curr_date = explode('-',$begin_parts[0]);

		//Get year and month from time stamp.
		$curr_year=$curr_date[0]-1;

		//get start date for last year and convert it to gmt and db format.
	    $begin=date($GLOBALS['timedate']->get_db_date_time_format(),mktime(0,0,0,1,1,$curr_year));

	    //get end date for last year  and convert it to gmt and db format.
        $end=date($GLOBALS['timedate']->get_db_date_time_format(),mktime(23,59,59,12,31,$curr_year));

		return $this->get_start_end_date_filter($layout_def,$begin,$end);
	}

	function queryFilterTP_this_year(& $layout_def) {
		global $timedate;
        $begin_timestamp = time();
        $begin = gmdate($GLOBALS['timedate']->get_db_date_time_format(), $begin_timestamp);
		$begin = $timedate->handle_offset($begin, $timedate->get_db_date_time_format(), true, $this->assigned_user);

        $begin_parts = explode(' ', $begin);
        $curr_date = explode('-',$begin_parts[0]);

		//Get year and month from time stamp.
		$curr_year=$curr_date[0];

		//get start date for this year and convert it to gmt and db format.
	    $begin=date($GLOBALS['timedate']->get_db_date_time_format(),mktime(0,0,0,1,1,$curr_year));

	    //get end date for this year  and convert it to gmt and db format.
        $end=date($GLOBALS['timedate']->get_db_date_time_format(),mktime(23,59,59,12,31,$curr_year));

		return $this->get_start_end_date_filter($layout_def,$begin,$end);
	}

	function queryFilterTP_next_year(& $layout_def) {
		global $timedate;
        $begin_timestamp = time();
        $begin = gmdate($GLOBALS['timedate']->get_db_date_time_format(), $begin_timestamp);
		$begin = $timedate->handle_offset($begin, $timedate->get_db_date_time_format(), true, $this->assigned_user);

        $begin_parts = explode(' ', $begin);
        $curr_date = explode('-',$begin_parts[0]);

		//Get year and month from time stamp.
		$curr_year=$curr_date[0]+1;


		//get start date for this year and convert it to gmt and db format.
	    $begin=date($GLOBALS['timedate']->get_db_date_time_format(),mktime(0,0,0,1,1,$curr_year));

	    //get end date for this year  and convert it to gmt and db format.
        $end=date($GLOBALS['timedate']->get_db_date_time_format(),mktime(23,59,59,12,31,$curr_year));

		return $this->get_start_end_date_filter($layout_def,$begin,$end);
	}

	
    
}

?>
