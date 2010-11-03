<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/SugarView.php');
require_once('modules/Tasks/Task.php');

class TasksViewDiscountapprove extends SugarView
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::SugarView();
    }

    public function display()
    {
        if (empty($_REQUEST['record'])) {
            sugar_die('No record ID passed.');
        }

        global $mod_strings, $app_strings, $app_list_strings, $timedate, $current_user, $beanList;

        $focus = new Task();
        $focus->retrieve($_REQUEST['record']);

        require_once(dirname(__FILE__) . "/../../../si_custom_files/MoofCartHelper.php");
        // get the proper class loaded
        // make sure that the current user is allowed to view this box
        $allowedList = MoofCartHelper::$taskApprovalChain[$focus->discount_chain_c];

        $chainMax = array(
            'is_subscription' => 25,
            'ca_subscription' => 25,
            'cs_d_na_subscription' => 20,
            'cs_d_emea_subscription' => 20,
            'cs_ps_emea_subscription' => 5,
            'cs_ps_world_subscription' => 5,
            'support' => 10,
            'professional_services' => 10,
        );

        foreach ($allowedList as $key => $user_id) {
            if ($user_id == $current_user->id) {
                // we have a user
                if (empty($focus->discount_approve_one_c)) {
                    $focus->discount_approve_one_c = $user_id;
                } else if (empty($focus->discount_approve_two_c)) {
                    $focus->discount_approve_two_c = $user_id;
                }

                $approve = ($focus->discount_percent_c <= $chainMax[$focus->discount_chain_c]);

                if ($key == count($allowedList) - 1 || $approve === true) {
                    // we have completed the task
                    $focus->status = "Completed";
                    $focus->assigned_user_id = $focus->discount_requested_by_id_c;

                    /**
                     * @var $obj Account|Opportunity
                     */
                    $obj = new $beanList[$focus->parent_type];
                    $obj->retrieve($focus->parent_id);

                    $obj->discount_pending_c = 0;
                    $obj->discount_approved_c = 1;

                    $obj->discount_approval_status_c = "Discount Approved by ";
                    if (!empty($focus->discount_approve_one_c)) {
                        $user = new User();
                        $user->retrieve($focus->discount_approve_one_c);

                        $obj->discount_approval_status_c .= $user->name;
                        unset($user);
                    }
                    if (!empty($focus->discount_approve_two_c)) {
                        $user = new User();
                        $user->retrieve($focus->discount_approve_two_c);

                        $obj->discount_approval_status_c .= " & " . $user->name;
                    }

                    $focus->description = $obj->discount_approval_status_c;

                    $obj->save(false);
                } else {
                    $focus->assigned_user_id = $allowedList[$key++];
                }

                $focus->save(false);
                break;
            }
        }

        header("Location: /index.php?module=Tasks&action=EditView&record=" . $focus->id);

    }

}