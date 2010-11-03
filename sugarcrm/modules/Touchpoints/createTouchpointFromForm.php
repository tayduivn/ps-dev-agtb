<?php

require_once('modules/Touchpoints/Touchpoint.php');
require_once("include/JSON.php");
global $current_user;

//set user info if not already set
require_once('modules/Users/User.php');
if(empty($current_user )){
	$current_user = new User();
	$current_user->retrieve('1');
}

//create new touchpoint
$tp = new Touchpoint();
//save touchpoint information for later processing
$json = new JSON(JSON_LOOSE_TYPE);
$tp->raw_data = $json->encode(from_html($_REQUEST));
//iterate through request and populate array
foreach($_REQUEST as $k=>$v){
	$tp->$k = $v;	
}

//prepend touchpoint name to description
	$tp->description = 'new touchpoint for '.$tp->title . ' '.$tp->first_name . ' '. $tp->last_name ;

$tp_id = $tp->save(false);

//call scrub helper for auto scrub  
//require_once('modules/Touchpoints/ScrubHelper.php');
//$sh = new ScrubHelper();
//$scrub_result = $sh->autoScrub($tp_id);

die("<br>Touchpoint was processed.<br>");
?>
