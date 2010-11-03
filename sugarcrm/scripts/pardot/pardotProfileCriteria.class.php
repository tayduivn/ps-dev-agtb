<?php
require_once('pardotData.abstract.php');

class pardotProfileCriteria extends pardotData {
    var $id;
    var $name;
    var $matches;
    function __construct() {
	$this->id = 0;	
	$this->name = '';
	$this->matches = '';
    }
}