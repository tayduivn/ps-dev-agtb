<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.detail.php');

class OpportunitiesViewDetail extends ViewDetail {
	function OpportunitiesViewDetail(){
		parent::ViewDetail();

		//** BEGIN CUSTOMIZATION EDDY :: ITTix 12220
	        //turn off normal display of subpanels, we will add subpanels back in manually from display function
        	$this->options['show_subpanels'] = false;
		//** END CUSTOMIZATION EDDY :: ITTix 12220

	}
	function display(){
		global $app_list_strings;
		global $mod_strings;
		global $current_user;
		
		if(!empty($this->bean->id)){
			require_once('custom/si_custom_files/checkRecordValidity.php');
			$checker = new checkRecordValidity();
			$checker->checkValidity($this->bean, 'custom/si_custom_files/oppCheckMeta.php');
			$warningString = '';
			if(!empty($checker->warningArray)){
				$warningString .= "<font color=red><i>Please resolve the following:</i><BR>\n";
				foreach($checker->warningArray as $string){
					$warningString .= "$string<BR>\n";
				}
				$warningString .= "</font><BR>\n";
				echo $warningString;
			}
		}
		
		if(!empty($this->bean->partner_assigned_to_c)){
			$account_query = "select name from accounts where id = '{$this->bean->partner_assigned_to_c}' and deleted = 0";
			$res = $GLOBALS['db']->query($account_query);
			if($res){
				$row = $GLOBALS['db']->fetchByAssoc($res);
				if(!empty($row)){
					$this->bean->partner_assigned_to_name = $row['name'];
				}
			}
		}
		
		$js = "\n<script>\n";		
		
		// Load up all the references to the panels based on the labels
		$d=$this->dv->defs['panels'];
		$panelArray = array();
		foreach($d as $panel_label=>$panel_data) {
			if(isset($mod_strings[strtoupper($panel_label)])){
				$panelArray[$mod_strings[strtoupper($panel_label)]] = $panel_label;
			}
		}
	
		// DEE CUSTOMIZATION - ITREQUEST 10914 - REMOVE THIS CODE AS SALES REPS WILL NOW NEED ACCESS TO VIEW 1% OPPS	
		// BEGIN DENY ACCESS TO CERTAIN RECORDS FOR LEAD QUAL
		/* 2009-09-04 --- SADEK - REMOVING THIS UNTIL WE HAVE A NEW ROLE FOR THE INDIA LEAD QUAL TEAM
		// If it's not a new record
		/*if(!empty($this->bean->fetched_row['id'])){
			if($GLOBALS['current_user']->check_role_membership('Lead Qual Rep')){
				if($this->bean->sales_stage != 'Initial_Opportunity' || $this->bean->created_by != $GLOBALS['current_user']->id){
					sugar_die('Error 8742: You do not have access to view this opportunity');
				}
			}
		}*/
		/*2009-09-04 --- SADEK - REMOVING THIS UNTIL WE HAVE A NEW ROLE FOR THE INDIA LEAD QUAL TEAM */
		// END DENY ACCESS TO CERTAIN RECORDS FOR LEAD QUAL
		// END DEE CUSTOMIZATION - ITREQUEST 10914 - REMOVE THIS CODE AS SALES REPS WILL NOW NEED ACCESS TO VIEW 1% OPPS
		// BEGIN SUGARINTERNAL CUSTOMIZATION - OPPORTUNITY VALIDATION
		
		if(isset($this->bean->sales_stage) && $this->bean->sales_stage == "Sales Ops Closed" && !$current_user->check_role_membership('Sales Operations Opportunity Admin') && !$current_user->check_role_membership('Finance')){
			$js .= "var edit_button_var = document.getElementById('edit_button'); if(edit_button_var != null) edit_button_var.disabled = 'disabled';\n";

                        // Unfortunately, the Delete button doesn't have an id, so we have to search for it on the page
                        $js .= "button_list = document.getElementsByName('Delete');
                                for ( i = 0; i < button_list.length; i++) {
                                        // we've found the proper Delete button
                                        if (button_list[i].value == 'Delete' && button_list[i].type == 'submit') {
                                                button_list[i].disabled = true;
                                        }
                                }\n";
		}
		if(isset($this->bean->sales_stage) && $this->bean->sales_stage == "Finance Closed" && !$current_user->check_role_membership('Finance')){
			$js .= "var edit_button_var = document.getElementById('edit_button'); if(edit_button_var != null) edit_button_var.disabled = 'disabled';\n";

                        // Unfortunately, the Delete button doesn't have an id, so we have to search for it on the page
                        $js .= "button_list = document.getElementsByName('Delete');
                                for ( i = 0; i < button_list.length; i++) {
                                        // we've found the proper Delete button
                                        if (button_list[i].value == 'Delete' && button_list[i].type == 'submit') {
                                                button_list[i].disabled = true;
                                        }
                                }\n";
		}
		// END SUGARINTERNAL CUSTOMIZATION - OPPORTUNITY VALIDATION

		// BEGIN jostrow MoofCart customization
		// See ITRequest #9622

		// If 'Revenue Type' is not 'Renewal,' we need to hide the 'Discount Code' field
		// Unfortunately, this means we need to scan through the detailviewdefs until we find it-- no good indexing exists here
		if ($this->bean->Revenue_Type_c != 'Renewal') {
			foreach($this->dv->defs['panels']['default'] as $row_index => $cols) {
				if (!empty($cols[0]) && !empty($cols[0]['name']) && $cols[0]['name'] == 'discount_code_c') {
					unset($this->dv->defs['panels']['default'][$row_index][0]);

					break;
				}
				elseif (!empty($cols[1]) && !empty($cols[1]['name']) && $cols[1]['name'] == 'discount_code_c') {
					unset($this->dv->defs['panels']['default'][$row_index][1]);

					break;
				}
			}
		}

		// clear the DetailView cache
		$this->dv->th->clearCache($this->module, 'DetailView.tpl');

		// END jostrow MoofCart customization
		
		$js .= "\n</script>\n";

        $this->ss->assign('APP_LIST_STRINGS', $app_list_strings);
	//** BEGIN CUSTOMIZATION EDDY :: ITTix 12405
	//retrieve max score stratight from the bean
	$this->ss->assign('MAX_SCORE', $this->bean->score_c);

		parent::display();
		echo $js;


	//** BEGIN CUSTOMIZATION EDDY :: ITTix 12220
        //We want to display subset of available panels, so we will call subpanel
        //object directly instead of using sugarview.
        $GLOBALS['focus'] = $this->bean;
        require_once('include/SubPanel/SubPanelTiles.php');
        $subpanel = new SubPanelTiles($this->bean, $this->module);
        //get available list of subpanels
        $alltabs=$subpanel->subpanel_definitions->get_available_tabs();
        if (!empty($alltabs)) {
            //iterate through list, and filter out all the  subpanel meant for opp_q quick edit form
            foreach ($alltabs as $key=>$name) {
	    	if ($name == 'for_opp_q' || $name == 'history_for_oppq') {
                    //exclude subpanels that are not prospectlists, emailmarketing, or tracked urls
                    $subpanel->subpanel_definitions->exclude_tab($name);
                }
            }
        }
        //show filtered subpanel list
        echo $subpanel->display();
	//** END CUSTOMIZATION EDDY :: ITTix 12220


	}
}
