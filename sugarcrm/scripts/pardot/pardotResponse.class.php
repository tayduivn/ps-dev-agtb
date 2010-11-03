<?php
/**
 * This wrapper class takes the raw xml data and massages it into simpleXML,
 * plus does some error handling and provides nice functions for checking the
 * status of a response.
 */
class pardotResponse {
    private $success;
    private $version;
    private $stat;
    private $data;
    private $raw_data;
    private $resultCount = 0;
	private $notices = true;
	
    function __construct($raw_data, $notices = true) {
	$this->stat = null;
	$this->version = null;
	$this->success = false;
	$this->notices = $notices;
	#echo "\n$raw_data\n";
	if ($raw_data) {
	    $this->raw_data = new SimpleXMLElement($raw_data);
	    if (empty($this->raw_data)) {
			if($notices){
				trigger_error('There was nothing in the curl response data', E_USER_NOTICE);
			}
	    }
	    $this->stat = (string) $this->raw_data['stat'];
	    $this->version = (string) $this->raw_data['version'];
	    if ($this->raw_data->result && $this->raw_data->result->total_results) {
		$this->resultCount = intval($this->raw_data->result->total_results);
	    }
	    if ('fail' == $this->stat) {
		$message = (string) $this->raw_data->err[0];
			if($notices){
				trigger_error('Curl call failed with the following message: ' . $message, E_USER_NOTICE);
			}
	    }
	    if ('ok' == $this->stat) {
		$this->success = true;
	    }
	    
	} else {
		if($notices){
		    trigger_error('Curl response data was empty', E_USER_NOTICE);
		}
	}
    }
    public function getResultCount() {
	return $this->resultCount;
    }
    public function success() {
	return $this->success;
    }
    public function getData() {
	return $this->raw_data;
    }
    public function displayNotices() {
	return $this->notices;
    }
}
