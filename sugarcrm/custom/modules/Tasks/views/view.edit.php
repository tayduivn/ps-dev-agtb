<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('modules/Tasks/views/view.edit.php');

class CustomTasksViewEdit extends TasksViewEdit
{
    function display()
    {
        global $mod_strings;
        $this->ev->th->clearCache($this->module, 'EditView.tpl');

        // BEGIN: Determine whether or not we display the Sales Management
        // Approval Checkbox
        $e = $this->ev->defs['panels'];
        $panelArray = array();
        foreach ($e as $panel_label => $panel_data) {
            if (isset($mod_strings[strtoupper($panel_label)])) {
                $panelArray[$mod_strings[strtoupper($panel_label)]] = $panel_label;
            }
        }
        if (!$GLOBALS['current_user']->check_role_membership('Sales Manager')) {
            foreach ($this->ev->defs['panels'] as $panel_index => $panel_rows) {
                if (isset($panelArray['Sales Management']) && $panel_index == $panelArray['Sales Management']) {
                    unset($this->ev->defs['panels'][$panel_index]);
                    break;
                }
            }
        }
        // END Determine whether or not we display the Sales Management fields
        // check for something
        if ($this->ev->focus->task_type_c == "discount_request") {
            require_once(dirname(__FILE__) . "/../../../si_custom_files/MoofCartHelper.php");
            // get the proper class loaded
            // make sure that the current user is allowed to view this box
            $allowedList = MoofCartHelper::$taskApprovalChain[$this->ev->focus->discount_chain_c];
            $allowedList[] = $this->ev->focus->discount_requested_by_id_c;
            global $beanList;
            global $current_user;
            // only allow the people in the chain and the person who created the request
            if (in_array($current_user->id, $allowedList)) {
                /**
                 * @var $obj Account|Opportunity
                 */
                $obj = new $beanList[$this->ev->focus->parent_type];
                $obj->retrieve($this->ev->focus->parent_id);

                $requested_user = new User();
                $requested_user->retrieve($this->ev->focus->discount_requested_by_id_c);

                $info = $requested_user->name . " Requested a Discount for " . $beanList[$this->ev->focus->parent_type] . " <a href='/index.php?module=" . $obj->module_dir . "&action=DetailView&record=" . $obj->id . "'>" . $obj->name . "</a><br />";
                $info .= "Discount Requested: " . $obj->discount_percent_c . "% ";
                if (isset($obj->discount_amount_c) && !empty($obj->discount_amount_c)) {
                    $info .= "($" . $obj->discount_amount_c . ")";
                }
                if ($obj->module_dir == "Opportunities") {
                    $info .= " -- Opportunity Amount: $" . $obj->amount;
                }
                $info .= "<br />";
                $info .= "Discount Valid: ";
                if ($obj->discount_no_expiration_c == 1) {
                    $info .= "Always";
                } else {
                    $info .= $obj->discount_valid_from_c . ' to ' . $obj->discount_valid_to_c;
                }

                $this->ev->defs['panels']['lbl_editview_panel1'][][0] = array('label' => "Discount Information", 'customCode' => $info);

                require_once("modules/DiscountCodes/DiscountCodes.php");
                // instantiate the discount code object
                $discount_code_bean = new DiscountCodes();
                // populate the dropdown list product_category_list, which was created blank, with the correct categories
                $product_categories = $discount_code_bean->getProductCategories();

                $applies_to = '';
                if ($obj->discount_to_c == "SpecificProduct") {
                    // get the producttemplate
                    $product = new Product();
                    $product->retrieve($obj->producttemplate_id1_c);
                    $applies_to = "Specific Product: " . $product->name;
                } else if ($obj->discount_to_c == "ProductCategory") {
                    // get the product category
                    $applies_to = "Any Product In Category: " . $product_categories[$obj->discount_to_prodcat_c];
                }
                if (!empty($applies_to)) {
                    $this->ev->defs['panels']['lbl_editview_panel1'][][0] = array('label' => "Discount Applies To", 'customCode' => $applies_to);
                }

                $applies_when = '';
                if ($obj->discount_when_c == "Always") {
                    $applies_when = "Always";
                } else if ($obj->discount_when_c == "CartTotalAtLeast") {
                    $applies_when = "Cart Total is At Least: $" . round($obj->discount_when_dollars_c, 2);
                } else if ($obj->discount_when_c == "SpecificProductInCart") {
                    // get the producttemplate
                    $product = new Product();
                    $product->retrieve($obj->producttemplate_id_c);
                    $applies_when = "Specific Product: " . $product->name;
                } else if ($obj->discount_when_c == "ProductBelongsToProdCat") {
                    // get the product category
                    $applies_when = "Any Product In Category: " . $product_categories[$obj->discount_when_prodcat_c];
                }
                if (!empty($applies_when)) {
                    $this->ev->defs['panels']['lbl_editview_panel1'][][0] = array('label' => "Discount Applies When", 'customCode' => $applies_when);
                }

                if ($obj->module_name == "Accounts") {
                    $perpetual_discount = ($obj->discount_perpetual_c == 1) ? 'Yes' : 'No';
                    $this->ev->defs['panels']['lbl_editview_panel1'][][0] = array('label' => "Perpetual Discount?", 'customCode' => $perpetual_discount);
                }
                
                $this->ev->defs['panels']['lbl_editview_panel1'][][0] = array('label' => "Discount Requested By", 'customCode' => $requested_user->name);

                foreach(MoofCartHelper::$taskApprovalChain[$this->ev->focus->discount_chain_c] as $chain_user) {
                    $c_user = new User();
                    $c_user->retrieve($chain_user);

                    $row_label = $c_user->name . " approves: ";
                    $row_value = "";
                    if($this->ev->focus->discount_approve_one_c == $c_user->id ||
                        $this->ev->focus->discount_approve_two_c == $c_user->id) {
                        $row_value .= "<font color='green'>Approved</font>";
                    } else if($this->ev->focus->discount_denied_c == $c_user->id) {
                        $row_value .= "<font color='red'>Denied</font>";
                    } else if($current_user->id == $c_user->id && $this->ev->focus->status != "Completed") {
                        $row_value .= '<button value="Approve" type="button" title="Approve" id="btnApprove" onClick="document.location.href=\'./index.php?module=Tasks&action=discountapprove&record='. $this->ev->focus->id . '\'">Approve</button> <button value="Deny" type="button" title="Deny" id="btnDeny" onClick="document.location.href=\'./index.php?module=Tasks&action=discountdeny&record='. $this->ev->focus->id . '\'">Deny</button>';
                    }

                    $this->ev->defs['panels']['lbl_editview_panel1'][][0] = array('label' => $row_label, 'customCode' => $row_value);
                }
            }
        }


        //DEE CUSTOMIZATION: ITREQUEST 7136
        if (!isset($this->ev->focus->id) && empty($this->ev->focus->id)) {
            $this->ev->focus->assigned_user_name = "";
            $this->ev->focus->assigned_user_id = "";
        }
        //END DEE CUSTOMIZATION

        parent::display();
    }
}
