<?php

class updateWorkLogOnSave {

    function update(&$bean, $event, $arguments) {
        if($event != "before_save" || empty($_POST['new_log']) === true) return false;

        global $current_user, $timedate, $sugar_config;

/*
** @author: DTam
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 17722
** Description: Standardize date format for ITRequest "Work Log" entries
** Wiki customization page: internalwiki.sjc.sugarcrm.pvt/index.php/SILogicHooksITRequests
*/
	$d = $sugar_config['default_date_format'];
       $t = $sugar_config['default_time_format'];
/* END SUGARINTERNAL CUSTOMIZATION */

        $log_date = date("$d \a\\t $t", time());

        if(!empty($bean->resolution)) {
            $msg = PHP_EOL . PHP_EOL;
        }
        $msg .= "<b>" . $current_user->name . " on " . $log_date . "</b>" . PHP_EOL . trim($_POST['new_log']);

        $bean->resolution .= $msg;

        return true;
    }
}
