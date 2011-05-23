<?php

class OpportunityLogicHooks {
	function setOppNumber(&$focus, $event, $arguments) {
		if($event == 'before_save'){
			if(empty($focus->fetched_row['id'])){
				$focus->name = IBMHelper::generateUniqueOppNumber();
				
				// START jvink customizations
				// set flag to synchronize contacts when duplicating
				if(isset($_REQUEST['duplicateSave']) && $_REQUEST['duplicateSave']
					&& isset($_REQUEST['duplicateId']) && $_REQUEST['duplicateId']) {
					$focus->duplicate_contacts_from_id = $_REQUEST['duplicateId'];
				}
			}
		}
	}
	
	function setBTTOptions(&$focus, $event, $arguments) {
		// Before save, prep the data for btt_options
		if($event == 'before_save'){
			$btt_opt_array = array();
			// there are 7 questions, but setting it to 20 in case we add more questions in the future
			for($i = 1; $i < 20; $i++){
				if(isset($_REQUEST['btt_options_'.$i])){
					$btt_opt_array[$i] = $_REQUEST['btt_options_'.$i];
				}
			}
	
			$focus->btt_options = '';
			if(!empty($btt_opt_array)){
				$focus->btt_options = json_encode($btt_opt_array);
			}
		}
		
		// After save, update the data
		if($event == 'after_retrieve'){
			// minor hack. would be better in view.detail.php, but this is more efficient for performance reasons
			if($_REQUEST['action'] == 'EditView'){
				$focus->btt_options = html_entity_decode($focus->btt_options);
				if(!empty($focus->btt_options)){
					$btt_opt_obj = json_decode($focus->btt_options);
					foreach($btt_opt_obj as $k => $v){
						$_REQUEST['btt_options_y_'.$k] = ($v == '1' ? 'CHECKED' : '');
						$_REQUEST['btt_options_n_'.$k] = ($v == '0' ? 'CHECKED' : '');
					}
				}
			}
		}
	}
	
	function setPrimaryContactRelationship(&$focus, $event, $arguments) {
		// Before save, collect the information necessary for the after_save
		if($event == 'before_save'){
			$focus->before_save_contact_id = '';
			if(!empty($focus->fetched_row['contact_id_c'])){
				$focus->before_save_contact_id = $focus->fetched_row['contact_id_c'];
			}
		}
		
		// After save, update the data
		if($event == 'after_save'){
			if(!isset($focus->contact_id_c)){
				$focus->contact_id_c = '';
			}
			
			// If the contact is not equal to the previous value, we remove the previous value
			if(!empty($focus->before_save_contact_id) && $focus->before_save_contact_id != $focus->contact_id_c){
				$focus->load_relationship('contacts');
				$focus->contacts->delete($focus->id, $focus->before_save_contact_id);
			}
			
			// If we have a value, we add it to the contacts subpanel on the opportunity
			if(!empty($focus->contact_id_c)){
				$focus->load_relationship('contacts');
				$rel_contacts = $focus->contacts->add($focus->contact_id_c, array('contact_role' => 'Primary Decision Maker'));
			}
		}
	}

	// START jvink customizations
	// Fix duplicate functionality
	function prepareDuplicate(&$focus, $event, $arguments) {
		
		// define fields to be populated during record duplication
		// every other field will be reset to it's default value
		$dup_fields = array(
			'id',
			'name',
			'date_entered',
			'date_modified',
			'modified_user_id',
			'modified_by_name',
			'created_by',
			'created_by_name',
			'description',
			'deleted',
			'created_by_link',
			'modified_user_link',
			'assigned_user_id',
			'assigned_user_link',
			'assigned_user_name',
			'team_id',
			'team_set_id',
			'team_count',
			'team_name',
			'team_link',
			'team_count_link',
			'teams',
			'account_name',
			'account_id',
			'lead_source',
			'amount',
			'amount_usdollar',
			'currency_id',
			'currency_name',
			'currency_symbol',
			'date_closed',
			'sales_stage',
			'probability',
			'accounts',
			'contacts',
			'currencies',
			'contact_id_c',
			'contact_c'
		);

		if(isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true') {

			// unset fields not defined in the above list
			foreach($focus->field_name_map  as $key => $value) {
				if(! in_array($key, $dup_fields)) {
					unset($focus->$key);
				}
			}
			// add "duplicate" into title to not confuse the enduser while duplicating records
			$focus->name = "Create duplicate from ".$focus->name;
			
			// ITAR should be unchecked by default
			$focus->itar_compliance_c = 0;
			
		}
		
		
	}
	// END jvink customizations

	// BEGIN jostrow customization
	// update the tags associated with this record

	function saveTags(&$focus, $event, $arguments) {

		if (isset($_REQUEST['tags'])) {
			IBMHelper::saveTags($focus->module_dir, $focus->id, $_REQUEST['tags']);
		}

	}

	// END jostrow customization

	// BEGIN jostrow customization
	// get tags associated with this record

	function getTags(&$focus, $event, $arguments) {

		$tags = IBMHelper::getRecordTags($focus);

		// encode as a Multienum so the EditView can process it correctly
		$focus->tags = encodeMultienumValue($tags);

	}

	// END jostrow customization

	// START jvink customization
	// duplicate related contacts
	function duplicateContacts(&$focus, $event, $arguments) {
		if($event == 'before_save'){
			if(empty($focus->fetched_row['id'])){
				if(isset($_REQUEST['duplicateSave']) && $_REQUEST['duplicateSave']
					&& isset($_REQUEST['duplicateId']) && $_REQUEST['duplicateId']) {

						// set this so it can be processed in after_save
						$focus->duplicate_contacts_from_id = $_REQUEST['duplicateId'];
				}
			}
		}
		
		if($event == 'after_save') {
			if(!empty($focus->duplicate_contacts_from_id)) {

				// init relationships
				$focus->load_relationship('contacts');

				// fetch original relationships
				// we skip contacts with a role defined, as the primary contact will get
				// populated by another hook
				$sql = 'SELECT contact_id 
							FROM opportunities_contacts 
							WHERE opportunity_id = "'.$focus->duplicate_contacts_from_id.'"
							AND (contact_role = "" OR contact_role IS NULL)
							AND deleted = 0';
				$q_rel = $focus->db->query($sql);
				while($rel = $focus->db->fetchByAssoc($q_rel)) {
					$focus->contacts->add($rel['contact_id']);
				}

			}
		}
	}
	// END jvink customization	
	
	// BEGIN sadek - SIMILAR OPPORTUNITY CALCULATOR
	function addOppsToSimCalcQueue(&$focus, $event, $arguments) {
		if($event == 'after_relationship_delete'){
			if($_REQUEST['action'] == 'DeleteRelationship' && 
				!empty($arguments['related_module']) && $arguments['related_module'] == 'ibm_revenueLineItems'
			){
				require_once('custom/IBMSimilarOpportunities.php');
				$GLOBALS['log']->info("OpportunityLogicHooks::addOppsToSimCalcQueue() delete_relate adding opp with id '{$arguments['id']}' to similar opp calc queue");
				IBMSimilarOpportunities::addToQueue($arguments['id']);
			}
		}
	}
	// END sadek - SIMILAR OPPORTUNITY CALCULATOR
	
	// BEGIN sadek - SugarAlerts logic hooks
	function sugarAlerts(&$focus, $event, $arguments){
		if($event == 'before_save'){
			// Sugar Alert for if the key deal changed. Who the alert is sent to is handled in SugarAlertsOppKeyDealFlagChanged() and SugarAlerts()
			if(isset($focus->fetched_row['key_deal_c']) && $focus->fetched_row['key_deal_c'] != $focus->key_deal_c){
				require_once('custom/include/SugarAlerts/Alerts/SugarAlertsOppKeyDealFlagChanged.php');
				$sa = new SugarAlertsOppKeyDealFlagChanged();
				$sa->handleAlert($focus);
			}
			
			// Sugar Alert for new opportunities. We set a flag if this is a new opportunity and call it after the data has been saved (after_save hook)
			if(empty($focus->fetched_row['id'])){
				$focus->SugarAlertsNewOpportunity = true;
			}
		}
		if($event == 'after_save'){
			if(isset($focus->SugarAlertsNewOpportunity) && $focus->SugarAlertsNewOpportunity == true){
				require_once('custom/include/SugarAlerts/Alerts/SugarAlertsNewOpportunity.php');
				$sa = new SugarAlertsNewOpportunity();
				$sa->handleAlert($focus);
			}
		}
	}
	// END sadek - SugarAlerts logic hooks
}
