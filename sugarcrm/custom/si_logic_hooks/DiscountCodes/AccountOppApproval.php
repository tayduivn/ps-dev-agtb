<?php

require_once(dirname(__FILE__) . "/../../si_custom_files/MoofCartHelper.php");

class AccountOppApproval
{

    protected $_product_categories = array();

    protected $_roleWithCategory = array(
        array('role' => 'Inside Sales Rep', 'category' => 'Subscriptions', 'chain' => 'is_subscription', 'max_rep_percent' => 10),
        array('role' => 'Customer Advocate Rep', 'category' => 'Subscriptions', 'chain' => 'ca_subscription', 'max_rep_percent' => 10),
        array('role' => 'Channel Sales Manager - North America', 'category' => 'Partner Fees', 'chain' => 'cs_d_na_subscription', 'max_rep_percent' => 10),
        array('role' => 'Channel Sales Manager - North America', 'category' => 'Subscriptions', 'chain' => 'cs_ps_world_subscription', 'max_rep_percent' => 10),
        array('role' => 'Channel Sales Manager - EMEA', 'category' => 'Partner Fees', 'chain' => 'cs_d_emea_subscription', 'max_rep_percent' => 10),
        array('role' => 'Channel Sales Manager - EMEA', 'category' => 'Subscriptions', 'chain' => 'cs_ps_emea_subscription', 'max_rep_percent' => 5),
		array('role' => 'Channel Sales Manager - APAC', 'category' => 'Partner Fees', 'chain' => 'cs_d_apac_subscription', 'max_rep_percent' => 10),
		array('role' => 'Channel Sales Manager - APAC', 'category' => 'Subscriptions', 'chain' => 'cs_ps_apac_subscription', 'max_rep_percent' => 5),
    );

    protected $_category = array(
        array('category' => 'Support', 'chain' => 'support', 'max_rep_percent' => 10),
        array('category' => 'Professional Services', 'chain' => 'professional_services', 'max_rep_percent' => 10),
    );

    protected $_roles = array(
        array('role' => 'Inside Sales Rep', 'category' => 0),
        array('role' => 'Customer Advocate Rep', 'category' => 1),
        array('role' => 'Channel Sales Manager - North America', 'category' => 2),
        array('role' => 'Channel Sales Manager - EMEA', 'category' => 4),
        array('role' => 'Channel Sales Manager - APAC', 'category' => 6),
    );

    public function startApproval(&$bean, $event, $arguments)
    {
        // we only want this to run on before save
        if ($event !== "before_save") return false;

        if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'removediscount') return false;

        // we don't want this to run if it's already pending or approved
        if ($bean->discount_approved_c == 1 || $bean->discount_pending_c == 1) return false;


        // sent the pending to 1 so we don't run this again
        $bean->discount_pending_c = 1;

        $this->loadProductCategories();

        $ret = false;
        // check categories with roles attached
        foreach ($this->_roleWithCategory as $role) {
            $ret = $this->check($bean, $role['role'], $role['category']);
            // if the check returns true then we exit out and process based on role.
            if ($ret === true) break;
        }

        // if nothing matched from above check just based on categories
        if ($ret === false) {
            foreach ($this->_category as $role) {
                $ret = $this->checkProductCategories($bean, $role['category']);
                if ($ret === true) break;
            }
        }

        if ($ret === false) {
            foreach ($this->_roles as $role) {
                $ret = $this->checkCurrentUserRole($role['role']);
                if ($ret === true) {
                    $role = $this->_roleWithCategory[$role['category']];
                    break;
                }
            }
        }

        if ($ret === false) {
            // no error as indicated below.  As this will only ever happen when no discount is filled out
            $bean->discount_pending_c = 0;
            return false;
            // we have an error so we need to email internal systems and

            /*global $current_user;

            $task = new Task();
            $task->name = "Error Finding Approval Path for Discount Request";
            $task->assigned_user_id = "3627d5b3-d7d6-bafb-add6-48f6641a90c4";
            $task->description = "Bean: " . $bean->moduleName . "<br />Record Id: " . $bean->id . "<br /><br >" .
                    $current_user->get_summary_text() . ' (' . $current_user->id . ') tried to request a discount on the above module and record and was not in any of the valid groups';
            $task->parent_id = $bean->id;
            if ($bean instanceof Account) {
                $task->parent_type = 'Accounts';
            } else if ($bean instanceof Opportunity) {
                $task->parent_type = 'Opportunities';
            } else {
                $task->parent_type = $bean->module_name;
            }
            $task->save(false);


            $bean->discount_approval_status_c = "Error Finding Approval Path for Discount Request";*/
            // fail out.
            return false;
        }

        $ret = $this->generateTask($bean, $role);

        if ($ret === true) {
            // this was auto approved so return true
            return true;
        } else if ($ret === false) {
            // there was an error
        } else if (is_guid($ret)) {
            // task was created
            return $ret;
        }
    }


    /**
     * Utility Function to make checking the rules easier
     *
     * @param Account|Opportunity $bean
     * @param string $role
     * @param string $product_category
     * @return bool
     */
    protected function check($bean, $role, $product_category)
    {
        if ($this->checkCurrentUserRole($role) &&
                $this->checkProductCategories($bean, $product_category)) {
            return true;
        }

        return false;
    }

    /**
     * Check the product category to make sure it's selected
     *
     * @param Account|Opportunity $bean
     * @param string $product_category
     * @return bool
     */
    protected function checkProductCategories($bean, $product_category)
    {
        $cat = $this->_product_categories[$product_category];
        if ($bean->discont_to_product_c == $cat ||
                $this->getDiscountProductCategoryFromBean($bean) == $cat) {
            return true;
        }
        return false;
    }

    /**
     * Get the category id for the product assigned to the bean.
     * If the product is empty return false
     *
     * @param Account|Opportunity $bean
     * @return bool|string
     */
    protected function getDiscountProductCategoryFromBean($bean)
    {
        if (empty($bean->producttemplate_id1_c)) return false;

        $product = new Product();
        $product->retrieve($bean->producttemplate_id1_c);

        return $product->category_id;
    }

    /**
     * @param Account/Opportunity $bean
     * @param array $role
     * @return void
     */
    protected function generateTask($bean, $role)
    {
        global $current_user;

        $task = new Task();
        $task->name = "Discount Approval Request -- " . $bean->discount_percent_c . '% off ' . $bean->name;
        $task->task_type_c = "discount_request";
        $task->status = "In Progress";
        $task->parent_id = $bean->id;
        if ($bean instanceof Account) {
            $task->parent_type = 'Accounts';
        } else if ($bean instanceof Opportunity) {
            $task->parent_type = 'Opportunities';
        } else {
            $task->parent_type = $bean->module_name;
        }

        $task->discount_chain_c = $role['chain'];
        $task->discount_requested_by_id_c = $current_user->id;

        $chain = MoofCartHelper::$taskApprovalChain[$role['chain']];
        $percent = $bean->discount_percent_c;

        switch ($role['chain']) {
            case 'is_subscription':
            case 'ca_subscription':
                // if it's less than or equal to 10 then approve it
                // other wise assign it to the first user in the chain
                if ($percent <= $role['max_rep_percent']) {
                    $bean->discount_pending_c = 0;
                    $bean->discount_approved_c = 1;
                    $bean->discount_approval_status_c = "Discount Approved";
                    unset($task);
                    return true;
                } else if ($percent > $role['max_rep_percent']) {
				    $bean->discount_approval_status_c = "Discount Pending Approval";
                    $task->assigned_user_id = $chain[0];
                }
                break;
            case 'cs_d_na_subscription':
            case 'cs_d_emea_subscription':
			case 'cs_d_apac_subscription':
                // if it's less than or equal to 10 then approve it
                // other wise assign it to the first user in the chain
                if ($percent <= $role['max_rep_percent']) {
                    $bean->discount_pending_c = 0;
                    $bean->discount_approved_c = 1;
                    $bean->discount_approval_status_c = "Discount Approved";
                    unset($task);
                    return true;
                } else if ($percent > $role['max_rep_percent']) {
                    $bean->discount_approval_status_c = "Discount Pending Approval";
                    $task->assigned_user_id = $chain[0];
                }
                break;
            case 'cs_ps_emea_subscription':
            case 'cs_ps_world_subscription':
			case 'cs_ps_apac_subscription':
                // if it's less than or equal to 5 then approve it
                // if it's more than 5 assign it to VP of sales
                if ($percent > 0 && $percent <= $role['max_rep_percent']) {
                    $bean->discount_pending_c = 0;
                    $bean->discount_approved_c = 1;
                    $bean->discount_approval_status_c = "Discount Approved";
                    unset($task);
                    return true;
                } else if ($percent > $role['max_rep_percent']) {
                    $bean->discount_approval_status_c = "Discount Pending Approval";
                    $task->assigned_user_id = $chain[0];
                }
                break;
            case 'support':
            case 'professional_services':
                // assign it to the first user in the chain
                $bean->discount_approval_status_c = "Discount Pending Approval";
                $task->assigned_user_id = $chain[0];
                break;
            default:
                // this should never happen
                // but if it does we just assign the task to internal systems
                $task->assigned_user_id = '3627d5b3-d7d6-bafb-add6-48f6641a90c4';
                break;
        }

        return $task->save(true);
    }

    /**
     * Return true if the user is in that role, false other wise.
     *
     * @param string $role
     * @return bool
     */
    protected function checkCurrentUserRole($role)
    {
        global $current_user;

        return $current_user->check_role_membership($role);
    }

    protected function loadProductCategories()
    {
        $db = &DBManagerFactory::getInstance();
        $this->_product_categories = array();
        // get the product categories
        $result = $db->query("SELECT id,name FROM product_categories WHERE deleted = 0");
        while ($row = $db->fetchByAssoc($result)) {
            $this->_product_categories[$row['name']] = $row['id'];
        }
    }

}
