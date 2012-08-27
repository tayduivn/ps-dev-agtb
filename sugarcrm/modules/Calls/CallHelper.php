<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * @param $focus
 * @param $field
 * @param $value
 * @param $view
 * @return string
 */
function getDurationMinutesOptions($focus, $field, $value, $view) {

    if (isset($_REQUEST['duration_minutes'])) {
        $focus->duration_minutes = $_REQUEST['duration_minutes'];
    }
    
	if (!isset($focus->duration_minutes)) {
		$focus->duration_minutes = $focus->minutes_value_default;
	}   
    
    global $timedate;
    //setting default date and time
	if (is_null($focus->date_start))
		$focus->date_start = $timedate->to_display_date(gmdate($timedate->get_date_time_format()));
	if (is_null($focus->duration_hours))
		$focus->duration_hours = "0";
	if (is_null($focus->duration_minutes))
		$focus->duration_minutes = "1";
	
    if($view == 'EditView' || $view == 'MassUpdate' || $view == "QuickCreate"
    //BEGIN SUGARCRM flav=pro ONLY
    || ($view == 'wirelessedit' && ACLField::hasAccess($field, $focus->module_dir, $GLOBALS['current_user']->id, $focus->isOwner($GLOBALS['current_user']->id)) > 1)
    //END SUGARCRM flav=pro ONLY
    ) {
       $html = '<select id="duration_minutes" ';
       if($view != 'MassUpdate' 
       		//BEGIN SUGARCRM flav=pro ONLY
       		&& $view != 'wirelessedit'
       		//END SUGARCRM flav=pro ONLY
       	 ) {
            $html .= 'onchange="SugarWidgetScheduler.update_time();" ';
       }

       $html .=  'name="duration_minutes">';
       $html .= get_select_options_with_id($focus->minutes_values, $focus->duration_minutes);
       $html .= '</select>';
       return $html;	
    }

    return $focus->duration_minutes;		
}

/**
 * @param $focus
 * @param $field
 * @param $value
 * @param $view
 * @return string
 *
 * @deprecated 6.5.0
 */
function getReminderTime($focus, $field, $value, $view) {

	global $current_user, $app_list_strings;
	$reminder_t = -1;
    
	if (!empty($_REQUEST['full_form']) && !empty($_REQUEST['reminder_time'])) {
		$reminder_t = $_REQUEST['reminder_time'];
	} else if (isset($focus->reminder_time)) {
		$reminder_t = $focus->reminder_time;
	} else if (isset($value)) {
        $reminder_t = $value;
    }

	if($view == 'EditView' || $view == 'MassUpdate' || $view == "SubpanelCreates" || $view == "QuickCreate"
	//BEGIN SUGARCRM flav=pro ONLY
    || $view == 'wirelessedit'
    //END SUGARCRM flav=pro ONLY
    ) {
		global $app_list_strings;
        $html = '<select id="reminder_time" name="reminder_time">';
        $html .= get_select_options_with_id($app_list_strings['reminder_time_options'], $reminder_t);
        $html .= '</select>';
        return $html;
    }
 
    if($reminder_t == -1) {
       return "";
    }
       
    return translate('reminder_time_options', '', $reminder_t);    
}

?>
