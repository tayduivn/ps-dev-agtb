<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.edit.php');

class OpportunitiesViewEdit extends ViewEdit {
    function OpportunitiesViewEdit() {
        parent::ViewEdit();
    }

    function display() {
        global $app_list_strings;
        global $mod_strings;
        global $current_user;

        /**
         * @author jwhitcraft
         * @project moofcart
         * @tasknum 55
         */
        require_once("modules/DiscountCodes/DiscountCodes.php");
        // instantiate the discount code object
        $discount_code_bean = new DiscountCodes();
        // populate the dropdown list product_category_list, which was created blank, with the correct categories
        $app_list_strings['product_category_c_list'] = $discount_code_bean->getProductCategories();
        /**
         * End Moofcart Customization
         */

        $js = "\n<script>\n";

        /**
         * @author jwhitcraft
         * @project moofcart
         * @task 54
         *
         * disabled the discount fields if the discount is approved or pending
         */
        $hideStatus = array('Close Won', 'Sales Ops Closed', 'Finance Closed');
        $this->ss->assign('SHOW_REMOVE_DISCOUNT', 1);
        if(in_array($this->bean->sales_stage, $hideStatus)) {
            $this->ss->assign('SHOW_REMOVE_DISCOUNT', 0);
        }
        if(in_array($this->bean->sales_stage, $hideStatus) || $this->bean->discount_pending_c == 1 || $this->bean->discount_approved_c == 1) {
            $ignoreKeys = array('discount_approved_c', 'discount_pending_c','discount_approval_status_c');
            foreach ($this->bean->field_name_map as $key => $field) {
                if (strrpos($key, 'discount_') === 0 && in_array($key, $ignoreKeys) == false) {
                    $js .= "\ndocument.getElementById('" . $key . "').disabled = true;\n";
                }
            }
        }
		
/*
** @author: DTam
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 12918, 17834
** Description: Remove old product types from edit view when creating new record
** Wiki customization page: 
*/
		if ((empty($this->bean->fetched_row['id']))) {
			unset($app_list_strings['opportunity_type_dom']['Sugar Network']);
			unset($app_list_strings['opportunity_type_dom']['Sugar Cube']);
			unset($app_list_strings['opportunity_type_dom']['Plug-ins']);
			unset($app_list_strings['opportunity_type_dom']['OS Support']);
			unset($app_list_strings['opportunity_type_dom']['Undecided']);
			unset($app_list_strings['opportunity_type_dom']['Sugar Express']);
		}
/* END SUGARINTERNAL CUSTOMIZATION */
		
        // Load up all the references to the panels based on the labels
        
        
        $e = $this->ev->defs['panels'];
        $panelArray = array();
        foreach ($e as $panel_label => $panel_data) {
            if (isset($mod_strings[strtoupper($panel_label)])) {
                $panelArray[$mod_strings[strtoupper($panel_label)]] = $panel_label;
            }
        }

        $depts_to_restrict_edit = array('Sales - Inside - West', 'Sales - Inside - Northeast', 'Sales - Inside - Southeast');
        if (!empty($this->bean->fetched_row['id'])) {
            $assigned_user = new User();
            $assigned_user->disable_row_level_security = true;
            $assigned_user->retrieve($this->bean->assigned_user_id);
            if (in_array($GLOBALS['current_user']->department, $depts_to_restrict_edit) && in_array($assigned_user->department, $depts_to_restrict_edit)) {
                if ($GLOBALS['current_user']->department != $assigned_user->department) {
                    sugar_die('Error 1731: You do not have access to edit this opportunity. The assigned user is in department ' . $assigned_user->department . ' and you are in department ' . $GLOBALS['current_user']->department);
                }
            }
        }
        //DEE CUSTOMIZATION - ITREQUEST 10914 - REMOVE THIS CODE AS SALES REPS WILL NOW NEED ACCESS TO VIEW 1% OPPS
        // BEGIN SUGARINTERNAL CUSTOMIZATION - IT REQUEST 3882 - FIELD LEVEL SECURITY ON AMOUNT FIELD
        /* 2009-09-04 --- SADEK - REMOVING THIS UNTIL WE HAVE A NEW ROLE FOR THE INDIA LEAD QUAL TEAM
          // If it's not a new record
          /*if(!empty($this->bean->fetched_row['id'])){
              if($GLOBALS['current_user']->check_role_membership('Lead Qual Rep')){
                  if($this->bean->sales_stage != 'Initial_Opportunity' || $this->bean->created_by != $GLOBALS['current_user']->id){
                      sugar_die('Error 8742: You do not have access to view this opportunity');
                  }
              }
          }*/
        /* 2009-09-04 --- SADEK - REMOVING THIS UNTIL WE HAVE A NEW ROLE FOR THE INDIA LEAD QUAL TEAM */
        // END SUGARINTERNAL CUSTOMIZATION - IT REQUEST 3882 - FIELD LEVEL SECURITY ON AMOUNT FIELD
        //END DEE CUSTOMIZATION - ITREQUEST 10914
		
        if (isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true') {
            /* Note: the keys in this array are simply labels for our convenience when
                * updating.
                */
            $requested_to_copy = array('Name' => 'name',
                'Account Name' => 'account_name',
                'Account Id' => 'account_id',
                'Amount' => 'amount',
                'amount_backup',
                'amount_usdollar',
                'currency_id',
                'Subscriptions' => 'users',
                'Type' => 'opportunity_type',
                'Team' => 'team_name',
                'Team ID' => 'team_id',
                'Team_link' => 'team_link',
                'Assigned to' => 'assigned_user_name',
                'Assigned to ID' => 'assigned_user_id'
            );
            $overwrite = array(
                'sales_stage' => 'Interested_Prospect',
                'probability' => '10',
                'lead_source' => 'Customer',
                'additional_training_credits_c' => '0',
                'next_step_dropdown_c' => 'blank',
            );
	
            foreach ($this->bean->field_name_map as $key => $array_def) {
                if (!in_array($key, $requested_to_copy)) {
                    $this->bean->$key = '';
                }
            }
            foreach ($overwrite as $key => $value) {
                $this->bean->$key = $value;
            }
        }


        // LAM CUSTOMIZATION IF ROLE IS NOT CHANNEL SALES MANAGER DISABLE ACCEPTED BY PARTNER
        if (!$current_user->check_role_membership('Channel Sales Manager')) {
            $js .= "document.getElementById('accepted_by_partner_c').disabled = true;\n";

        }
        // END LAM CUSTOMIZATION
        if (!$current_user->check_role_membership('Channel Sales Manager') && !$current_user->check_role_membership('Sales Manager')) {
            // jwhitcraft customization - ITR 15706
            $js .= "document.getElementById('conflict_c').disabled = true;\n";
            $js .= "document.getElementById('conflict_type_c').disabled = true;\n";
            // end customization - jwhitcraft
        }

        // IF THE SALES STAGE IS FINANCE CLOSED AND THE USER IS NOT IN FINANCE, NO ACCESS
        if (isset($this->bean->sales_stage) && $this->bean->sales_stage == "Finance Closed" && !$current_user->check_role_membership('Finance')) {
            sugar_die("<i>This Opportunity has been set to 'Finance Closed'. Please contact the Finance department if you'd like to make any changes.</i>");
        }

        // OPPORTUNITY VALIDATION - Remove sales stages based on role
        if ( /*false && */
        !$current_user->check_role_membership('Finance')) {
            if (isset($this->bean->sales_stage) && $this->bean->sales_stage == 'Finance Closed')
                $js .= "document.getElementById('sales_stage').disabled = true;\n"; //echo "Sales stage disabled<BR>"; //$xtpl->assign("SALES_STAGE_DISABLE", 'disabled');
            else
                unset($app_list_strings['sales_stage_dom']['Finance Closed']);
        }
        if ( /*false && */
                !$current_user->check_role_membership('Sales Operations Opportunity Admin') && !$current_user->check_role_membership('Finance')) {
            if (isset($this->bean->sales_stage) && $this->bean->sales_stage == 'Sales Ops Closed') {
                $js .= "document.getElementById('sales_stage').disabled = true;\n"; //echo "Sales stage disabled<BR>"; //$xtpl->assign("SALES_STAGE_DISABLE", 'disabled');
            } else {
                unset($app_list_strings['sales_stage_dom']['Sales Ops Closed']);
            }
        }

        // DEE CUSTOMIZATION - ITREQUEST 10914
        if (empty($this->bean->fetched_row['id']) || !isset($this->bean->sales_stage) || $this->bean->sales_stage != 'Initial_Opportunity') {
            unset($app_list_strings['sales_stage_dom']['Initial_Opportunity']);
        }
        // END DEE CUSTOMIZATION - ITREQUEST 10914

        if (empty($this->bean->fetched_row['id']) || !isset($this->bean->sales_stage) || $this->bean->sales_stage != 'Closed Won') {
            unset($app_list_strings['sales_stage_dom']['Closed Won']);
        }


        if (!$GLOBALS['current_user']->check_role_membership('Finance') && !$GLOBALS['current_user']->check_role_membership('Sales Operations Opportunity Admin')) {
            echo '<script type="text/javascript" src="custom/include/javascript/custom_javascript.js"></script>' . "\n";
            require('custom/si_custom_files/meta/opportunityRevenueTypeOppTypeMap.php');
            $js .= "var do_rtop = true;\n";
            $js .= "var rtop_array = new Array();\n";
            foreach ($opportunityRevenueTypeOppTypeMap as $revenue_type => $rev_arr) {
                $js .= "rtop_array['{$revenue_type}'] = new Array();\n";
                foreach ($rev_arr as $opp_type_key => $opp_type_value) {
                    $js .= "rtop_array['{$revenue_type}']['{$opp_type_key}'] = '{$opp_type_value}';\n";
                }
            }
            $js .= "setOpportunityTypesFromRevType(do_rtop, rtop_array);\n";
            $js .= "
				document.getElementById('opportunity_type').onchange = function() {
					setGlobalVarCurrentOpportunityType();
				}\n";
        }
        else {
            $js .= "var do_rtop = false;\n";
        }

        // BEGIN IT REQUEST 3880 - Sales Reps cannot go backwards in sales stage
        /*if( !$current_user->check_role_membership('Finance') &&
              !$current_user->check_role_membership('Sales Operations Opportunity Admin') &&
              !$current_user->check_role_membership('Sales Manager')
          ){
              require('custom/si_custom_files/meta/OpportunitiesSalesStageConfig.php');
              if(!empty($this->bean->sales_stage)){
                  foreach($sales_stage_map as $stage_index => $percent){
                      /* BEGIN IT REQUEST 10196 - Allow to roll
                       * back from 98% to 90%
                       */
        /*			if($stage_index == $this->bean->sales_stage
                         || ($stage_index == 'Committed' && $this->bean->sales_stage == 'Closed Won')) {
                          /* END IT REQUEST 10196 */
        /*				break;
                      }
                      unset($app_list_strings['sales_stage_dom'][$stage_index]);
                  }
              }
          }*/
        // END IT REQUEST 3880 - Sales Reps cannot go backwards in sales stage

        /*
          // BEGIN: Hide the training panel if the user is not allowed
          include('support/trainingUsers.php');
          if(!is_admin($GLOBALS['current_user']) && !in_array($current_user->id, $trainingUsers)){
              foreach($this->ev->defs['panels'] as $panel_index => $panel_rows){
                  if($panel_index == $panelArray['Learning Credits Information']){
                      unset($this->ev->defs['panels'][$panel_index]);
                      break;
                  }
              }
          }
          // END: Hide the training panel if the user is not allowed
          */

        // BEGIN jostrow customization
        // See ITRequest #7156: Need to modify the Opportunities screen so SLC field is only editable by Sales Ops/Finance

	/*This field is no longer in use. going ot remove it from edit view and made it not required - DEE 10/22/2010 */
        /*if (!$current_user->check_role_membership('Finance') && !$current_user->check_role_membership('Sales Operations')) {
            $js .= "document.getElementById('additional_training_credits_c').disabled = true;\n";
        }*/

        // END jostrow customization

        // BEGIN jostrow customization
        // See ITRequest #7123: restrict edit access to "order type" in opportunities module

        if (!$current_user->check_role_membership('Finance')) {
            $js .= "document.getElementById('order_type_c').disabled = true;\n";
        }

        // END jostrow customization

        // BEGIN jostrow MoofCart customization
        // See ITRequest #9622

        $js .= "
			function checkDiscountCodeVisibility() {
				if (document.getElementById('Revenue_Type_c').value != 'Renewal') {
					document.getElementById('discount_code_c_label').innerHTML = '';
					document.getElementById('discount_code_c').style.visibility = 'hidden';
					document.getElementById('discount_code_c').disabled = true;
				}
				else {
					document.getElementById('discount_code_c_label').innerHTML = '{$mod_strings['LBL_DISCOUNT_CODE']}:';
					document.getElementById('discount_code_c').style.visibility = 'visible';
					document.getElementById('discount_code_c').disabled = false;
				}
			}

			checkDiscountCodeVisibility();
			document.getElementById('Revenue_Type_c').onchange = function() {
				checkDiscountCodeVisibility();
				setOpportunityTypesFromRevType(do_rtop, rtop_array);
			}
		";

        // END jostrow MoofCart customization

        $js .= "\n</script>\n";
		
        parent::display();
        echo $js;
    }
}
