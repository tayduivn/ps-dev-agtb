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
 * $Id: iFrame.php 45521 2009-03-25 07:40:22Z martinhu $
 ********************************************************************************/


// Contact is used to store customer information.
class iFrame extends SugarBean
{
	// Stored fields
	var $id;
	var $url;
	var $name;
	var $deleted;
	var $status = 1;
	var $placement='' ;
	var $date_entered;
	var $created_by;
	var $type;
	var $date_modified;
	var $table_name = "iframes";
	var $object_name = "iFrame";
	var $module_dir = 'iFrames';
	var $new_schema = true;
 
	function iFrame()
	{
		parent::SugarBean();
		//BEGIN SUGARCRM flav=pro ONLY 
		$this->disable_row_level_security =true;
		//END SUGARCRM flav=pro ONLY 
	}

	function get_xtemplate_data(){
		$return_array = array();
		global $current_user;
		foreach($this->column_fields as $field)
		{
			$return_array[strtoupper($field)] = $this->$field;
		}
				if(is_admin($current_user)){
					$select = translate('DROPDOWN_PLACEMENT', 'iFrames');
					$return_array['PLACEMENT_SELECT'] = get_select_options_with_id($select, $return_array['PLACEMENT'] );
				}else{
					$select = translate('DROPDOWN_PLACEMENT', 'iFrames');
					$shortcut = array('shortcut'=> $select['shortcut']);
					$return_array['PLACEMENT_SELECT'] = get_select_options_with_id($shortcut, '');
				}

				if(is_admin($current_user)){
					$select = translate('DROPDOWN_TYPE', 'iFrames');
					$return_array['TYPE_SELECT'] = get_select_options_with_id($select, $return_array['TYPE'] );
				}else{
					$select = translate('DROPDOWN_TYPE', 'iFrames');
					$personal = array('personal'=> $select['personal']);
					$return_array['TYPE_SELECT'] = get_select_options_with_id($personal, '');
				}
				if(!empty($select[$return_array['PLACEMENT']])){
					$return_array['PLACEMENT'] = $select[$return_array['PLACEMENT']];
				}

		return $return_array;
	}

		function get_list_view_data()
	{
		$ret_array = parent::get_list_view_array();
		if(!empty($ret_array['STATUS']) && $ret_array['STATUS'] > 0){
			 $ret_array['STATUS'] = '<input type="checkbox" class="checkbox" style="checkbox" checked disabled>';
		}else{
			$ret_array['STATUS'] = '<input type="checkbox" class="checkbox" style="checkbox" disabled>'	;
		}
		if(strlen($ret_array['URL']) > 63){
			$ret_array['URL'] = substr($ret_array['URL'], 0, 50) . '...' . substr($ret_array['URL'],-10);
		}
		$ret_array['CREATED_BY'] = get_assigned_user_name($this->created_by);
		$ret_array['PLACEMENT'] = translate('DROPDOWN_PLACEMENT', 'iFrames', $ret_array['PLACEMENT']);
				$ret_array['TYPE'] = translate('DROPDOWN_TYPE', 'iFrames', $ret_array['TYPE']);
		return $ret_array;

	}



	function lookup_frames($placement){
			global $current_user;
			$frames = array();
			if(!empty($current_user->id)){
				$id = $current_user->id;
			}else{
			    if(!empty($GLOBALS['sugar_config']['login_nav'])){
			        $id = -1;
			    }else{
				    return $frames;
			    }
			}
			$query = 'SELECT placement,name,id,url from '  .$this->table_name . " WHERE deleted=0 AND status=1 AND (placement='$placement' OR placement='all') AND (type='global' OR (type='personal' AND created_by='$id')) ORDER BY iframes.name";
			$res = $this->db->query($query);
			
			while($row = $this->db->fetchByAssoc($res)){
				$frames[$row['name']] = array($row['id'], $row['url'], $row['placement'],"iFrames",$row['name']);
			}
			return $frames;

	}

		function lookup_frame_by_record_id($record_id){
			global $current_user;
			if(isset($current_user)){
				$id = $current_user->id;
			}else{
				$id = -1;
			}
			$query = 'SELECT placement,name,id,url from '  .$this->table_name . " WHERE id = '$record_id' and  deleted=0 AND status=1 AND (placement='tab' OR placement='all') AND (type='global' OR (type='personal' AND created_by='$id'))";
			$res = $this->db->query($query);
			$frames = array();
			while($row = $this->db->fetchByAssoc($res)){
				$frames[$row['name']] = array($row['id'], $row['url'], $row['placement'],"iFrames",$row['name']);
			}
			return $frames;

	}
    
    function create_export_query($order_by, $where) {
        global $current_user;
        $user_id = $current_user->id;
        $custom_join = $this->custom_fields->getJOIN(true, true,$where);
        $query = "SELECT iframes.*";
        if($custom_join){
            $query .= $custom_join['select'];
        }
        $query .= " FROM iframes ";
        if($custom_join){
            $query .= $custom_join['join'];
        }

        $where_auto = " iframes.deleted = 0 AND (type='personal' AND created_by='$user_id')";

        if ($where != "")
            $query .= " WHERE $where AND ".$where_auto;
        else
            $query .= " WHERE ".$where_auto;

        if ($order_by != "")
            $query .= " ORDER BY $order_by";
        else
            $query .= " ORDER BY iframes.name";

        return $query;
    }

}


?>