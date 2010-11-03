<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************

 * Description: This file is used to override the default Meta-data EditView behavior
 * to provide customization specific to the Contacts module.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/json_config.php');
require_once('include/MVC/View/SugarView.php');

class ps_TimesheetsViewList extends SugarView {
   
 	function ps_TimesheetsViewList(){
 		parent::SugarView();
 	}
 	
 	/**
 	 * display
 	 * 
 	 * We are overridding the display method to manipulate the sectionPanels.
 	 * If portal is not enabled then don't show the Portal Information panel.
 	 */
 	function display() {
 		global $mod_strings, $app_list_strings, $timedate, $current_user;
        //echo "<div class='moduleTitle'><h2>{$mod_strings['LBL_MODULE_NAME']}</h2></div>";
        //$this->ss->display('file:modules/ps_Timesheets/tpls/EditView.tpl');
        $this->ss->assign('MOD', $mod_strings);
        $this->ss->assign('ldelim', "{");
        $this->ss->assign('rdelim', "}");
        
 	    $this->ss->assign('CALENDAR_DATEFORMAT', $timedate->get_cal_date_format());
        $this->ss->assign('USER_DATEFORMAT', $timedate->get_user_date_format());
        $time_format = $timedate->get_user_time_format();
        $this->ss->assign('TIME_FORMAT', $time_format);

        $date_format = $timedate->get_cal_date_format();
        $time_separator = ":";
        if(preg_match('/\d+([^\d])\d+([^\d]*)/s', $time_format, $match)) {
           $time_separator = $match[1];
        }

        // Create Smarty variables for the Calendar picker widget
        $t23 = strpos($time_format, '23') !== false ? '%H' : '%I';
        if(!isset($match[2]) || $match[2] == '') {
          $this->ss->assign('CALENDAR_FORMAT', $date_format . ' ' . $t23 . $time_separator . "%M");
        } else {
          $pm = $match[2] == "pm" ? "%P" : "%p";
          $this->ss->assign('CALENDAR_FORMAT', $date_format . ' ' . $t23 . $time_separator . "%M" . $pm);
        }
        
		$json = getJSONobj();
		require_once('include/QuickSearchDefaults.php');
		$qsd = new QuickSearchDefaults();
		$qsd->setFormName('EditView');
		$sqs_objects = array('account_name' => $qsd->getQSParent());
		$sqs_objects['account_name']['populate_list'] = array('account_name', 'account_id');
		$sqs_objects['account_name']['required_list'] = array('account_id');
		$quicksearch_js = '<script type="text/javascript" language="javascript">sqs_objects = ' . $json->encode($sqs_objects) . ';enableQS(false);</script>';
		$this->ss->assign('QS_JAVASCRIPT', $quicksearch_js);

		$activity_type_options = $app_list_strings['activity_type_list'];
		$this->ss->assign('ACTIVITY_TYPE_OPTIONS', get_select_options_with_id($activity_type_options, ''));
		
		$month_options = $app_list_strings['dom_cal_month_long'];
		$current_month = date('n');
		$this->ss->assign('MONTH_OPTIONS', get_select_options_with_id($month_options, $current_month));
		
		$year_options = $app_list_strings['years_list'];
		$current_year = date('Y');
		$this->ss->assign('YEAR_OPTIONS', get_select_options_with_id($year_options, $current_year));
		
		$user_list = array();
		$sql = "SELECT id, user_name FROM users WHERE id = '{$current_user->id}' OR reports_to_id = '{$current_user->id}'";
		$result = $this->bean->db->query($sql);
		while($row = $this->bean->db->fetchByAssoc($result))
			$user_list[$row['id']] = $row['user_name'];
		$this->ss->assign('USER_LIST_OPTIONS', get_select_options_with_id($user_list, $current_user->id));
		
        $this->ss->display('file:modules/ps_Timesheets/tpls/ListView.tpl');
 		
 		parent::display();
 	}
 	
}

?>