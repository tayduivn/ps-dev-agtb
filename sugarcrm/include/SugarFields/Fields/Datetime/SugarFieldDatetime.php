<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');

class SugarFieldDatetime extends SugarFieldBase {

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
        
        if(!isset($displayParams['hiddeCalendar'])) {
           $displayParams['hiddeCalendar'] = false;   
        } 
       
        $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
        //jchi , bug #24557 , 10/31/2008
        if(isset($vardef['name']) && ($vardef['name'] == 'date_entered' || $vardef['name'] == 'date_modified')){
        	return $this->fetch($this->findTemplate('DetailView'));
        }
        //end
        return $this->fetch($this->findTemplate('EditView'));
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
    	return $this->fetch($this->findTemplate('WirelessEditView'));
    }   
    //END SUGARCRM flav=pro || flav=sales ONLY

    function getSearchViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {
        if($this->isRangeSearchView($vardef)) {
           $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
           $id = isset($displayParams['idName']) ? $displayParams['idName'] : $vardef['name'];
           $this->ss->assign('original_id', "{$id}");
           $this->ss->assign('id_range', "range_{$id}");
           $this->ss->assign('id_range_start', "start_range_{$id}");
           $this->ss->assign('id_range_end', "end_range_{$id}");
           $this->ss->assign('id_range_choice', "{$id}_range_choice");        
           return $this->fetch('include/SugarFields/Fields/Datetimecombo/RangeSearchForm.tpl');
        }   
    	return $this->getSmartyView($parentFieldArray, $vardef, $displayParams, $tabindex, 'EditView');    
    }    
    
    public function save(&$bean, &$inputData, &$field, &$def, $prefix = '') {
        global $timedate;
        if ( !isset($inputData[$prefix.$field]) ) {
            return;
        }

        $offset = strlen(trim($inputData[$prefix.$field])) < 11 ? false : true;
	    $bean->$field = $timedate->to_db_date($inputData[$prefix.$field], $offset);    	
    }
    
    /**
     * @see SugarFieldBase::importSanitize()
     */
    public function importSanitize(
        $value,
        $vardef,
        $focus,
        ImportFieldSanitize $settings
        )
    {
        global $timedate;
        
        $format = $settings->dateformat . ' ' . $settings->timeformat;
        
        if ( !$timedate->check_matching_format($value, $format) ) {
            // see if adding a valid time at the end makes it work
            list($dateformat,$timeformat) = explode(' ',$format);
            $value .= ' ' . date($timeformat,0);
            if ( !$timedate->check_matching_format($value, $format) ) {
                return false;
            }
        }
        
        if ( !$settings->isValidTimeDate($value, $format) )
            return false;
        
        $value = $timedate->swap_formats(
            $value, $format, $timedate->get_date_time_format());
        $value = $timedate->handle_offset(
            $value, $timedate->get_date_time_format(), false, $GLOBALS['current_user'], $settings->timezone);
        $value = $timedate->swap_formats(
            $value, $timedate->get_date_time_format(), $timedate->get_db_date_time_format() );
        
        return $value;
    }
}
?>