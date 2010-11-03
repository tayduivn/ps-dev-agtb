<?php


//process and reassign bean based on passed in parameters.  Save only if save flag is passed in
//value passed in should be an array containing (as applicable) user, company,  campaign_name, campaign_id,  Call_Back_c, contactPartnertrial_name, registered_eval_c, first_name, email1
function reassign_touchpoint(&$bean, $params='', $save=false){
	//process only if passed in params are a non empty array
	if(empty($params) || !is_array($params)){
		//params are empty or not sent in as an array, nothing to process
		return;
	}
	
	//declare users array for use in processing below
	$users = array(
		'cheeto' => array('name'=>'admin', 'pass'=>'0192023a7bbd73250516f069df18b500', 'id'=>'1'),
		'orbit' => array('name'=>'Leads_Nurture', 'pass'=>'c78aab0cfad330b27b43d9129a190b15', 'id'=>'92045137-c16f-daf2-6848-4a1739b31a16'),
		'pluto' => array('name'=>'Leads_HotMktg', 'pass'=>'9139a90b1bd94dc57d8b57a5815a2353', 'id'=>'c15afb6d-a403-b92a-f388-4342a492003e'),
		'partner' => array('name'=>'Leads_Partner', 'pass'=>'6e323a7c0254589792d270f9f63f37bd', 'id'=>'2c780a1f-1f07-23fd-3a49-434d94d78ae5'),
	);

	/*
	** @author: Deepali
	** This array of campaigns is for touchpoints that should be assigned to Leads_Partner
	*/
	$partner_campaigns = array(
		'9e4d2191-a2dd-54f7-8aaa-4a53c3d2a9a9',
	);
	/*
	** END
	*/

	//if this is from installer forms
	if($params['user'] =='orbit' ){
		if(isset($params['company'])  && !empty($params['company'])) {
			$bean->company_name = $params['company'];
			unset($params['company']);
		}

		if ($params['campaign_name'] == 'Product Registration') {
			$bean->campaign_id = '3f5959cd-739b-2bc6-4610-43742ca4148e';
			if(isset($params['company'])){
				$bean->company_name = $params['company']; //<--changed to company name from account_name. account_name does not exist in touchpoints
				unset($params['company']);
			}
	   	}

		// this is for new installer registeration form 
		else if ($params['campaign_name'] == 'OS' || $params['campaign_name'] == 'CE') {
			$bean->campaign_id = 'dc47492a-87aa-445f-24f8-45a433a0e344';
			//this is to assign installer leads to new, rating A and to hotMktg if user has requested a call back 
			if(isset($params['Call_Back_c']) && !empty($params['Call_Back_c'])) {
				$bean->rating = 'A';
				$bean->status = 'New';
				$bean->assigned_user_id = $users['pluto']['id'];
			}
		}
		else if ($params['campaign_name'] == 'PRO') {
			$bean->campaign_id = 'e9e69850-a9f0-8cac-d93c-45a433781111';
		}
		else if ($params['campaign_name'] == 'ENT') {
			$bean->campaign_id = 'd4db3cc4-6056-f8c9-b6b9-45a433389548';
		}

		else if($params['campaign_id'] == "6a1a911f-5770-efcd-4476-475f5c695902"){
			$bean->assigned_user_id = $users['orbit']['id'];
			if(isset($params['Call_Back_c']) && !empty($params['Call_Back_c'])) {//<---call_back_c is different case in touchpoints
				$bean->rating = 'A'; //<<--lead_rating_c is not in touchpoint, there is a rating
				$bean->status = 'New';
				$bean->assigned_user_id = $users['pluto']['id'];
			}

		}

		//this is from sugarcrm.com forms
		else {
			$bean->assigned_user_id = $users['pluto']['id'];
		}
	}



	//if this is from marketing form //<--
	if($params['user'] =='pluto' ){
		$bean->assigned_user_id = $users['pluto']['id'];
	}

	/*
	** @author: Deepali
	** IF touchpoint is interested in Partner programs OR has signed up on a partner campaign
	** THEN assign touchpoint to Leads_Partner
	*/
	if((isset($params['contactPartner']) && $params['contactPartner'] == 1) 
		|| (isset($params['campaign_id']) && in_array($params['campaign_id'], $partner_campaigns))
		|| (isset($params['campaign_name']) && in_array($params['campaign_name'], $partner_campaigns))
	){
                $bean->assigned_user_id = $users['partner']['id'];
	}

	//save only if save flag was passed in	
	if($save){
		$bean->save();
	}
}


?>
