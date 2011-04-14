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


class SugarWidgetFieldDateTimecombo extends SugarWidgetFieldDateTime {
	var $reporter;
	var $assigned_user=null;

    function SugarWidgetFieldDateTimecombo(&$layout_manager) {
        parent::SugarWidgetFieldDateTime($layout_manager);
        $this->reporter = $this->layout_manager->getAttribute('reporter');
    }

	function queryFilterOn(& $layout_def) {
		global $timedate;
		if($this->getAssignedUser()) {
			$ontime = $timedate->handle_offset($layout_def['input_name0'], $timedate->get_db_date_time_format(), false, $this->assigned_user);
		}
		else {
			$ontime = $layout_def['input_name0'];
		}

		//BEGIN SUGARCRM flav=ent ONLY 
		if ($this->reporter->db->dbType == 'oci8') {
			return $this->_get_column_select($layout_def)."=TO_DATE('".$this->reporter->db->quote($ontime)."','YYYY-MM-DD hh24:mi:ss')  \n";
		} else {
			//END SUGARCRM flav=ent ONLY 
			return $this->_get_column_select($layout_def)."='".$this->reporter->db->quote($ontime)."' \n";
			//BEGIN SUGARCRM flav=ent ONLY 
		}
		//END SUGARCRM flav=ent ONLY 
	}
    function queryFilterBefore(& $layout_def) {
        global $timedate;

        if($this->getAssignedUser()) {
            $begin = $timedate->handle_offset($layout_def['input_name0'], $timedate->get_db_date_time_format(), false, $this->assigned_user);
        }
        else {
            $begin = $layout_def['input_name0'];
        }

        //BEGIN SUGARCRM flav=ent ONLY
        if ($this->reporter->db->dbType == 'oci8') {
            return $this->_get_column_select($layout_def)."< TO_DATE('".$this->reporter->db->quote($begin)."', 'yyyy-mm-dd hh24:mi:ss')\n";
        } else {
            //END SUGARCRM flav=ent ONLY
            return $this->_get_column_select($layout_def)."<'".$this->reporter->db->quote($begin)."'\n";
            //BEGIN SUGARCRM flav=ent ONLY
        }
        //END SUGARCRM flav=ent ONLY

    }

    function queryFilterAfter(& $layout_def) {
        global $timedate;

        if($this->getAssignedUser()) {
            $begin = $timedate->handle_offset($layout_def['input_name0'] , $timedate->get_db_date_time_format(), false, $this->assigned_user);
        }
        else {
            $begin = $layout_def['input_name0'];
        }

        //BEGIN SUGARCRM flav=ent ONLY
        if ($this->reporter->db->dbType == 'oci8') {
            return $this->_get_column_select($layout_def)."> TO_DATE('".$this->reporter->db->quote($begin)."', 'yyyy-mm-dd hh24:mi:ss')\n";
        } else {
            //END SUGARCRM flav=ent ONLY
            return $this->_get_column_select($layout_def).">'".$this->reporter->db->quote($begin)."'\n";
            //BEGIN SUGARCRM flav=ent ONLY
        }
        //END SUGARCRM flav=ent ONLY
    }
	//TODO:now for date time field , we just search from date start to date end. The time is from 00:00:00 to 23:59:59
	//If there is requirement, we can modify report.js::addFilterInputDatetimesBetween and this function
	function queryFilterBetween_Datetimes(& $layout_def) {
		global $timedate;
		if($this->getAssignedUser()) {
			$begin = $timedate->handle_offset($layout_def['input_name0'], $timedate->get_db_date_time_format(), false, $this->assigned_user);
			$end = $timedate->handle_offset($layout_def['input_name2'], $timedate->get_db_date_time_format(), false, $this->assigned_user);
		}
		else {
			$begin = $layout_def['input_name0'];
			$end = $layout_def['input_name1'];
		}
		//BEGIN SUGARCRM flav=ent ONLY 
		if ($this->reporter->db->dbType == 'oci8') {
			return "(".$this->_get_column_select($layout_def).">=TO_DATE('".$this->reporter->db->quote($begin)."','yyyy-mm-dd hh24:mi:ss') AND \n ".$this->_get_column_select($layout_def)."<=TO_DATE('".$this->reporter->db->quote($end)."','yyyy-mm-dd hh24:mi:ss'))\n";
		} else {
			//END SUGARCRM flav=ent ONLY 
			return "(".$this->_get_column_select($layout_def).">='".$this->reporter->db->quote($begin)."' AND \n".$this->_get_column_select($layout_def)."<='".$this->reporter->db->quote($end)."')\n";
			//BEGIN SUGARCRM flav=ent ONLY 
		}
		//END SUGARCRM flav=ent ONLY 
	}
	
    function queryFilterNot_Equals_str(& $layout_def) {
        global $timedate;

        if($this->getAssignedUser()) {
            $begin = $timedate->handle_offset($layout_def['input_name0'], $timedate->get_db_date_time_format(), false, $this->assigned_user);
            $end = $timedate->handle_offset($layout_def['input_name0'], $timedate->get_db_date_time_format(), false, $this->assigned_user);
        }
        else {
            $begin = $layout_def['input_name0'];
            $end = $layout_def['input_name0'];
        }

        if ($this->reporter->db->dbType == 'oci8') {
        //BEGIN SUGARCRM flav=ent ONLY
            return "NVL( TO_CHAR(".$this->_get_column_select($layout_def)."),'0')  = '0' OR \n(".$this->_get_column_select($layout_def)."< TO_DATE('".$this->reporter->db->quote($begin)."','yyyy-mm-dd hh24:mi:ss') OR ".$this->_get_column_select($layout_def).">TO_DATE('".$this->reporter->db->quote($end)."','yyyy-mm-dd hh24:mi:ss') )\n";
            //END SUGARCRM flav=ent ONLY

        } elseif ($this->reporter->db->dbType == 'mssql'){
            return "(".$this->_get_column_select($layout_def)."<'".$this->reporter->db->quote($begin)."' OR ".$this->_get_column_select($layout_def).">'".$this->reporter->db->quote($end)."')\n";

        }else{
            return "ISNULL(".$this->_get_column_select($layout_def).") OR \n(".$this->_get_column_select($layout_def)."<'".$this->reporter->db->quote($begin)."' OR ".$this->_get_column_select($layout_def).">'".$this->reporter->db->quote($end)."')\n";
        }
    }	
}
?>

