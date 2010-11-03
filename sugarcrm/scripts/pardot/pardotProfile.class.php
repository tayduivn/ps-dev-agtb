<?php
require_once('pardotData.abstract.php');
require_once('pardotProfileCriteria.class.php');

class pardotProfile extends pardotData {
    var $id;
    var $name;
    var $profile_criteria;
    function __construct() {
	$this->id = 0;	
	$this->name = '';
	$this->profile_criteria = array();
    }
    function loadFromSimpleXML($simpleXML) {
	$success = false;
	foreach ($simpleXML->children() as $key => $value) {
	    $key = (string) $key;
	    switch ($key) {
	    case 'profile_criteria' :
		$profile_criteria = new pardotProfileCriteria();
		if ($profile_criteria->loadFromSimpleXML($value)) {
		    $success = true;
		    $this->profile_criteria[] = $profile_criteria;
		} else {
		    trigger_error('There was a problem loading a profile_criteria', E_USER_NOTICE);
		}
		break;
	    default :
		$value = (string) $value;
		$this->$key = $value;
		$success = true;
		break;
	    }
	}

	return $success;
    }
}