<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');

class SugarFieldDatetimecombo extends SugarFieldBase {

    function getEditViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {        
        // Create Smarty variables for the Calendar picker widget
        if(!isset($displayParams['showMinutesDropdown'])) {
           $displayParams['showMinutesDropdown'] = false;	
        }
        
        if(!isset($displayParams['showHoursDropdown'])) {
           $displayParams['showHoursDropdown'] = false;	
        }
        
        if(!isset($displayParams['showNoneCheckbox'])) {
           $displayParams['showNoneCheckbox'] = false;	
        }
        
        if(!isset($displayParams['showFormats'])) {
           $displayParams['showFormats'] = false;	
        }
       
        global $timedate;
        $displayParams['dateFormat'] = $timedate->get_cal_date_format();

        $displayParams['timeFormat'] = $timedate->get_user_time_format();
        $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);       
        return $this->fetch('include/SugarFields/Fields/Datetimecombo/EditView.tpl');
    }
    
    function getImportViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) 
    {
        $displayParams['showFormats'] = true;
        return $this->getEditViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex);
    }
	
    function getSearchViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {
    	// Create Smarty variables for the Calendar picker widget
        if(!isset($displayParams['showMinutesDropdown'])) {
           $displayParams['showMinutesDropdown'] = false;	
        }
        
        if(!isset($displayParams['showHoursDropdown'])) {
           $displayParams['showHoursDropdown'] = false;	
        }
        
        if(!isset($displayParams['showNoneCheckbox'])) {
           $displayParams['showNoneCheckbox'] = false;	
        }
        
        if(!isset($displayParams['showFormats'])) {
           $displayParams['showFormats'] = false;	
        }
       
        global $timedate;
        $displayParams['dateFormat'] = $timedate->get_cal_date_format();

        $displayParams['timeFormat'] = $timedate->get_user_time_format();
        $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
        return $this->fetch('include/SugarFields/Fields/Datetimecombo/SearchView.tpl'); 
    }
    
    //BEGIN SUGARCRM flav=pro || flav=sales ONLY
    function getWirelessEditViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {
    	global $timedate;
    	$datetime_prefs = $GLOBALS['current_user']->getUserDateTimePreferences();
    	$datetime = explode(' ', $vardef['value']);

		// format date and time to db format
		$date_start = $timedate->swap_formats($datetime[0], $datetime_prefs['date'], $timedate->dbDayFormat);
    	$time_start = $timedate->swap_formats($datetime[1], $datetime_prefs['time'], $timedate->dbTimeFormat);

    	// pass date parameters to smarty
    	if ($datetime_prefs['date'] == 'Y-m-d' || $datetime_prefs['date'] == 'Y/m/d' || $datetime_prefs['date'] == 'Y.m.d'){
    		$this->ss->assign('field_order', 'YMD');
    	}
    	else if ($datetime_prefs['date'] == 'd-m-Y' || $datetime_prefs['date'] == 'd/m/Y' || $datetime_prefs['date'] == 'd.m.Y'){
    		$this->ss->assign('field_order', 'DMY');
    	}
    	else{
    		$this->ss->assign('field_order', 'MDY');
    	}
    	$this->ss->assign('date_start', $date_start);
    	// pass time parameters to smarty
    	$use_24_hours = stripos($datetime_prefs['time'], 'a') ? false : true;
    	$this->ss->assign('time_start', $time_start);
    	$this->ss->assign('use_meridian', $use_24_hours);
    	
    	$this->setup($parentFieldArray, $vardef, $displayParams, $tabindex, false);
    	return $this->fetch('include/SugarFields/Fields/Datetimecombo/WirelessEditView.tpl');
    }   
    //END SUGARCRM flav=pro || flav=sales ONLY
    
    public function getEmailTemplateValue($inputField, $vardef, $displayParams = array(), $tabindex = 0){
        
        return $GLOBALS['timedate']->to_display_date_time($inputField, true, false, new stdClass() );
    }
    
    public function save(&$bean, &$inputData, &$field, &$def, $prefix = '') {
        global $timedate;
        if ( !isset($inputData[$prefix.$field]) ) {
            //$bean->$field = '';
            return;
        }
        
        if(strpos($inputData[$prefix.$field], ' ') > 0) {
	        $bean->$field = $timedate->to_db($inputData[$prefix.$field]);
        } else {
        	$GLOBALS['log']->error('Field ' . $prefix.$field . ' expecting datetime format, but got value: ' . $inputData[$prefix.$field]);
	        //Default to assume date format value
        	$bean->$field = $timedate->to_db_date($inputData[$prefix.$field]);    	
        }
    } 
}
?>