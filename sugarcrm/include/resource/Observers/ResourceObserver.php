<?php
/*********************************************************************************
 * The contents of this file are subject to
 ********************************************************************************/

/**
 * ResourceObserver.php
 * This class serves as the base class for the notifier/observable pattern used
 * by the resource management framework.
 */
class ResourceObserver {

var $module;
var $limit;

function ResourceObserver($module) {
	$this->module = $module;
}

function setLimit($limit) {
	$this->limit = $limit;
}

function notify($msg = '') {
    if($this->dieOnError) {
       die($GLOBALS['app_strings']['ERROR_NOTIFY_OVERRIDE']);
    } else {
       echo($GLOBALS['app_strings']['ERROR_NOTIFY_OVERRIDE']);	
    }
}	
	
}

?>
