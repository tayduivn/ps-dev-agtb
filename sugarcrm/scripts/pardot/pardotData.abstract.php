<?php
abstract class pardotData {
    public function loadFromSimpleXML($simpleXML) {
	$success = false;
	if (!is_a($simpleXML, 'SimpleXMLElement')) {
	    trigger_error('argument must be a SimpleXMLElement object, not a(n) ' . get_class($simpleXML), E_USER_NOTICE);
	} else {
	    foreach ($simpleXML->children() as $key => $value) {
		$key = (string) $key;
		$value = (string) $value;
		$this->$key = $value;
		$success = true;
	    }
	}
	return $success;
    }
}