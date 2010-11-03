<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.edit.php');

class AccountsViewEdit extends ViewEdit
{
    function AccountsViewEdit()
    {
        parent::ViewEdit();
    }

    function display()
    {
        global $app_list_strings;
        global $mod_strings;

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

        // Load up all the references to the panels based on the labels
        $e = $this->ev->defs['panels'];
        $panelArray = array();
        foreach ($e as $panel_label => $panel_data) {
            if (isset($mod_strings[strtoupper($panel_label)])) {
                $panelArray[$mod_strings[strtoupper($panel_label)]] = $panel_label;
            }
        }

        /**
         * @author jwhitcraft
         * @project moofcart
         * @task 54
         *
         * disabled the discount fields if the discount is approved or pending
         */
        if($this->bean->discount_pending_c == 1 || $this->bean->discount_approved_c == 1) {
            $ignoreKeys = array('discount_amount_c', 'discount_approved_c', 'discount_pending_c','discount_approval_status_c');
            foreach ($this->bean->field_name_map as $key => $field) {
                if (strrpos($key, 'discount_') === 0 && in_array($key, $ignoreKeys) == false) {
                    $js .= "\ndocument.getElementById('" . $key . "').disabled = true;\n";
                }
            }
        }

        // IF YOU CHANGE THIS ARRAY OF ACCOUNT TYPES, PLEASE CHANGE checkAccountTypeDependentDropdown IN include/javascript/custom_javascript.js
        $reference_code_types = array('Partner', 'Partner-Ent', 'Partner-Pro');
        if (!$GLOBALS['current_user']->check_role_membership('Sales Operations') || !in_array($this->bean->account_type, $reference_code_types)) {
            $js .= "\ndocument.getElementById('reference_code_c').disabled = true;\n";
        }
        if ($GLOBALS['current_user']->check_role_membership('Sales Operations')) {
            $this->ss->assign('ref_code_param', 'true');
        }
        else {
            $this->ss->assign('ref_code_param', 'false');
	    /*
	    ** @author: dtam
	    ** SUGARINTERNAL CUSTOMIZATION
	    ** ITRequest #:19859
	    ** Description: make resell discount only editable by sales ops
	    */
	    $js .= "\ndocument.getElementById('resell_discount').disabled = true;\n";
	    /* END SUGARINTERNAL CUSTOMIZATION */
        }

        // BEGIN: Determine whether or not we display the DCE fields
        $this->ev->th->clearCache($this->module, 'EditView.tpl');
        if (($GLOBALS['current_user']->user_name != 'sadek' && $this->bean->id == 'b80d0cc0-1622-eebb-998e-4147933a7b54') || (!$GLOBALS['current_user']->check_role_membership('DCE Field Access') && $GLOBALS['current_user']->department != 'Customer Support')) {
            foreach ($this->ev->defs['panels'] as $panel_index => $panel_rows) {
                if ($panel_index == $panelArray['DCE Information']) {
                    unset($this->ev->defs['panels'][$panel_index]);
                    break;
                }
            }
        }
        // END: Determine whether or not we display the DCE fields


		// ITR #19685 jbartek -> Determine whether or not we display the customer_msa_not_required_c
		if(!$GLOBALS['current_user']->check_role_membership('Sales Operations') && !$GLOBALS['current_user']->check_role_membership('Sales Operations Opportunity Admin') && !is_admin($GLOBALS['current_user'])) {
			$field_name = 'customer_msa_not_required_c';			
			foreach($this->ev->defs['panels']['lbl_account_information'] AS $key => $set) {			
				foreach($set AS $k => $info) {
					if($info['name'] == $field_name) {
						unset($this->ev->defs['panels']['lbl_account_information'][$key][$k]);
					}
				}
			}
			
		}
		// END: ITR #19685


        // BEGIN: Set support to none if the account type is partner or past
        $noSupportArray = array('Affiliate', 'Past Customer', 'Past Partner');
        if (!empty($this->bean->Partner_Type_c) && isset($this->bean->Partner_Type_c) && in_array($this->bean->Partner_Type_c, $noSupportArray)) {
            $this->bean->Support_Service_Level_c = 'no_support';
        }
        // END: Set support to none if the account type is partner or past

        // BEGIN: Hide the training panel if the user is not allowed
        include('support/trainingUsers.php');
        /* BEGIN SUGARINTERNAL CUSTOMIZATION BUGFIX */
        if (!is_admin($GLOBALS['current_user']) && !in_array($GLOBALS['current_user']->id, $trainingUsers)) {
            /* END SUGARINTERNAL CUSTOMIZATION BUGFIX */
            foreach ($this->ev->defs['panels'] as $panel_index => $panel_rows) {
                if ($panel_index == $panelArray['Learning Credits Information']) {
                    unset($this->ev->defs['panels'][$panel_index]);
                    break;
                }
            }
        }
        // END: Hide the training panel if the user is not allowed

        // DEE CUSTOMIZATION ITREQUEST 12501
        global $current_user;
        if (isset($current_user->department) && ($current_user->department != 'Customer Support')) {
            $js .= "document.getElementById('code_customized_by_c[]').disabled = true;\n";
        }
        // END DEE CUSTOMIZATION

        /**
         * @author jwhitcraft
         * @project moofcart
         * @tasknum 109
         */
        if($current_user->check_role_membership('Finance') === false
                && $current_user->check_role_membership('Sales Operations') === false) {
            $js .= "document.getElementById('po_order_5k_c').disabled = true;\n";
        }

        $js .= "\n</script>\n";
        parent::display();
        echo $js;
    }
}
