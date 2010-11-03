<?php
/**
 * This file creates a touchpoint based on a prospect id
 */

chdir(dirname(__FILE__));
require_once('pardotApi.class.php');
require_once('pardotLogger.class.php');

chdir('../..');
define('sugarEntry', true);



require_once('include/entryPoint.php');
require_once('modules/Touchpoints/Touchpoint.php');
require_once('modules/Users/User.php');

$pardot = pardotApi::magic();

$requested_prospect_id = '63890345';

$prospect = $pardot->getProspectById($requested_prospect_id);
if ($prospect) {
		$first_campaign = array();
		if(isset($prospect->visitor_activities)){
			foreach($prospect->visitor_activities as $activity_object){
				if(isset($activity_object->form_handler_id)){
						print_r($activity_object);
				}
			}
		}
}
